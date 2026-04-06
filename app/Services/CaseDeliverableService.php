<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\CaseDeliverable;
use App\Models\CaseReturn;
use App\Models\CertificationRequest;
use App\Models\StaffActivityLog;
use Illuminate\Support\Facades\DB;

class CaseDeliverableService
{
    /**
     * ============================================================
     * CaseDeliverableService
     *
     * 結果物（VR / IR / RN）の発行・保留・失効を一元管理する。
     *
     * ★ 最重要ルール（このService経由でのみ操作すること）:
     *   RN が ISSUED かつ is_active = true の間、
     *   VR / IR は PENDING のまま保留。
     *   RN が VOID になって初めて VR / IR を ISSUED に移行できる。
     * ============================================================
     */

    // -------------------------------------------------------
    // 認証申請受付時: VR/IR/RN を初期状態で生成
    // -------------------------------------------------------

    /**
     * certification_requests 作成時に呼び出す。
     * VR/IR = NOT_READY、RN = NOT_REQUIRED で生成する。
     */
    public function createInitial(CertificationRequest $request): void
    {
        DB::transaction(function () use ($request) {

            // VR（個人会員向け認証済み履歴書）
            CaseDeliverable::create([
                'case_no'                  => $request->case_no,
                'certification_request_id' => $request->id,
                'deliverable_type'         => CaseDeliverable::TYPE_VR,
                'visibility_scope'         => CaseDeliverable::SCOPE_APPLICANT_VIEW,
                'deliverable_status'       => CaseDeliverable::STATUS_NOT_READY,
                'is_active'                => true,
            ]);

            // IR（企業向け調査報告書）
            CaseDeliverable::create([
                'case_no'                  => $request->case_no,
                'certification_request_id' => $request->id,
                'deliverable_type'         => CaseDeliverable::TYPE_IR,
                'visibility_scope'         => CaseDeliverable::SCOPE_COMPANY_VIEW,
                'deliverable_status'       => CaseDeliverable::STATUS_NOT_READY,
                'is_active'                => true,
            ]);

            // RN（内部差し戻し通知）- 初期は NOT_REQUIRED
            CaseDeliverable::create([
                'case_no'                  => $request->case_no,
                'certification_request_id' => $request->id,
                'deliverable_type'         => CaseDeliverable::TYPE_RN,
                'visibility_scope'         => CaseDeliverable::SCOPE_INTERNAL_ONLY,
                'deliverable_status'       => 'NOT_REQUIRED',
                'is_active'                => true,
            ]);

            // certification_requests の deliverable_*_status も同期
            $request->update([
                'deliverable_vr_status' => CaseDeliverable::STATUS_NOT_READY,
                'deliverable_ir_status' => CaseDeliverable::STATUS_NOT_READY,
                'deliverable_rn_status' => 'NOT_REQUIRED',
            ]);
        });
    }

    // -------------------------------------------------------
    // AI審査完了時: VR/IR を PENDING に移行
    // -------------------------------------------------------

    /**
     * AI審査完了時（ready_for_review = true → AI処理後）に呼び出す。
     * RN がなければ VR/IR を PENDING に移行する。
     */
    public function moveToPending(CertificationRequest $request, int $caseReviewId): void
    {
        DB::transaction(function () use ($request, $caseReviewId) {

            // アクティブなRNが存在する場合は保留のまま（移行しない）
            if ($request->hasActiveReturnNotice()) {
                return;
            }

            foreach ([CaseDeliverable::TYPE_VR, CaseDeliverable::TYPE_IR] as $type) {
                $deliverable = $this->getActive($request->id, $type);
                if ($deliverable && $deliverable->deliverable_status === CaseDeliverable::STATUS_NOT_READY) {
                    $deliverable->update([
                        'deliverable_status' => CaseDeliverable::STATUS_PENDING,
                        'case_review_id'     => $caseReviewId,
                    ]);
                }
            }

            // certification_requests と同期
            $request->update([
                'deliverable_vr_status' => CaseDeliverable::STATUS_PENDING,
                'deliverable_ir_status' => CaseDeliverable::STATUS_PENDING,
            ]);

            // 監査ログ
            AuditLog::recordAi(AuditLog::ACTION_VR_PENDING, $request->case_no);
            AuditLog::recordAi(AuditLog::ACTION_IR_PENDING, $request->case_no);
        });
    }

    // -------------------------------------------------------
    // 差し戻し発生時: RN を ISSUED・VR/IR を PENDING に保留
    // -------------------------------------------------------

    /**
     * 差し戻し発生時に呼び出す。
     * RN = ISSUED・VR/IR = PENDING（既にPENDINGの場合は維持）。
     */
    public function issueReturnNotice(
        CertificationRequest $request,
        CaseReturn           $caseReturn,
        int                  $caseReviewId
    ): void {
        DB::transaction(function () use ($request, $caseReturn, $caseReviewId) {

            // 既存のRNを無効化
            CaseDeliverable::where('certification_request_id', $request->id)
                ->where('deliverable_type', CaseDeliverable::TYPE_RN)
                ->where('is_active', true)
                ->update(['is_active' => false]);

            // 新しいRNを発行
            $rn = CaseDeliverable::create([
                'case_no'                  => $request->case_no,
                'certification_request_id' => $request->id,
                'case_review_id'           => $caseReviewId,
                'deliverable_type'         => CaseDeliverable::TYPE_RN,
                'visibility_scope'         => CaseDeliverable::SCOPE_INTERNAL_ONLY,
                'deliverable_status'       => CaseDeliverable::STATUS_ISSUED,
                'generated_at'             => now(),
                'is_active'                => true,
                'json_payload'             => [
                    'case_return_id'      => $caseReturn->id,
                    'return_reason_code'  => $caseReturn->return_reason_code,
                    'return_reason_summary' => $caseReturn->return_reason_summary,
                ],
            ]);

            // VR/IR を PENDING に保留（NOT_READYの場合もPENDINGに変更）
            foreach ([CaseDeliverable::TYPE_VR, CaseDeliverable::TYPE_IR] as $type) {
                $deliverable = $this->getActive($request->id, $type);
                if ($deliverable) {
                    $deliverable->update([
                        'deliverable_status' => CaseDeliverable::STATUS_PENDING,
                    ]);
                }
            }

            // certification_requests と同期
            $request->update([
                'deliverable_vr_status'    => CaseDeliverable::STATUS_PENDING,
                'deliverable_ir_status'    => CaseDeliverable::STATUS_PENDING,
                'deliverable_rn_status'    => CaseDeliverable::STATUS_ISSUED,
                'latest_return_id'         => $caseReturn->id,
                'internal_return_required' => true,
            ]);

            // 監査ログ
            AuditLog::recordAi(
                AuditLog::ACTION_RN_ISSUED,
                $request->case_no,
                null,
                ['serial_no' => $rn->id]
            );
            AuditLog::recordAi(AuditLog::ACTION_VR_PENDING, $request->case_no);
            AuditLog::recordAi(AuditLog::ACTION_IR_PENDING, $request->case_no);
        });
    }

    // -------------------------------------------------------
    // 承認時: VR/IR を ISSUED に移行
    // -------------------------------------------------------

    /**
     * 審査管理部が最終承認した際に呼び出す。
     * ★ アクティブなRNが存在する場合は発行不可（例外を投げる）。
     *
     * @throws \RuntimeException RNが存在して発行できない場合
     */
    public function issueAll(CertificationRequest $request, int $staffUserId): void
    {
        // ★ RN保留チェック（最重要）
        if ($request->hasActiveReturnNotice()) {
            throw new \RuntimeException(
                "case_no [{$request->case_no}]: アクティブなRNが存在するため VR/IR を発行できません。"
                . " 差し戻しを解消してから再度承認してください。"
            );
        }

        DB::transaction(function () use ($request, $staffUserId) {

            $vrSerialNo = $this->generateSerialNo(CaseDeliverable::TYPE_VR);
            $irSerialNo = $this->generateSerialNo(CaseDeliverable::TYPE_IR);

            // VR を ISSUED に移行
            $vr = $this->getActive($request->id, CaseDeliverable::TYPE_VR);
            if ($vr && $vr->deliverable_status === CaseDeliverable::STATUS_PENDING) {
                $vr->update([
                    'deliverable_status' => CaseDeliverable::STATUS_ISSUED,
                    'serial_no'          => $vrSerialNo,
                    'generated_at'       => now(),
                    'expires_at'         => now()->addMonths(3), // VRは3ヶ月有効
                ]);
            }

            // IR を ISSUED に移行
            $ir = $this->getActive($request->id, CaseDeliverable::TYPE_IR);
            if ($ir && $ir->deliverable_status === CaseDeliverable::STATUS_PENDING) {
                $ir->update([
                    'deliverable_status' => CaseDeliverable::STATUS_ISSUED,
                    'serial_no'          => $irSerialNo,
                    'generated_at'       => now(),
                ]);
            }

            // certification_requests と同期
            $request->update([
                'deliverable_vr_status' => CaseDeliverable::STATUS_ISSUED,
                'deliverable_ir_status' => CaseDeliverable::STATUS_ISSUED,
            ]);

            // 監査ログ
            AuditLog::recordHuman(
                AuditLog::ACTION_VR_ISSUED,
                $request->case_no,
                ['serial_no' => $vrSerialNo]
            );
            AuditLog::recordHuman(
                AuditLog::ACTION_IR_ISSUED,
                $request->case_no,
                ['serial_no' => $irSerialNo]
            );

            // スタッフ操作ログ
            StaffActivityLog::record(
                'issue_deliverables',
                'CertificationRequest',
                $request->id,
                [
                    'case_no'     => $request->case_no,
                    'description' => "VR({$vrSerialNo}) / IR({$irSerialNo}) を発行",
                ]
            );
        });
    }

    // -------------------------------------------------------
    // 差し戻し解消時: RN を VOID に → VR/IR 発行可能状態へ
    // -------------------------------------------------------

    /**
     * 差し戻しが解消された際に呼び出す（再審査完了・再承認時）。
     * RN を VOID にし、VR/IR の発行ブロックを解除する。
     */
    public function resolveReturnNotice(
        CertificationRequest $request,
        int                  $staffUserId
    ): void {
        DB::transaction(function () use ($request, $staffUserId) {

            // アクティブなRNを全て VOID に
            CaseDeliverable::where('certification_request_id', $request->id)
                ->where('deliverable_type', CaseDeliverable::TYPE_RN)
                ->where('deliverable_status', CaseDeliverable::STATUS_ISSUED)
                ->where('is_active', true)
                ->update([
                    'deliverable_status' => CaseDeliverable::STATUS_VOID,
                    'is_active'          => false,
                ]);

            // certification_requests と同期
            $request->update([
                'deliverable_rn_status'    => CaseDeliverable::STATUS_VOID,
                'internal_return_required' => false,
            ]);

            // 監査ログ
            AuditLog::recordHuman(
                AuditLog::ACTION_RETURN_RESOLVED,
                $request->case_no
            );
        });
    }

    // -------------------------------------------------------
    // 却下時: VR/IR を VOID に
    // -------------------------------------------------------

    /**
     * 審査管理部が却下した際に呼び出す。
     */
    public function voidAll(CertificationRequest $request): void
    {
        DB::transaction(function () use ($request) {

            CaseDeliverable::where('certification_request_id', $request->id)
                ->whereIn('deliverable_type', [CaseDeliverable::TYPE_VR, CaseDeliverable::TYPE_IR])
                ->where('is_active', true)
                ->update([
                    'deliverable_status' => CaseDeliverable::STATUS_VOID,
                    'is_active'          => false,
                ]);

            $request->update([
                'deliverable_vr_status' => CaseDeliverable::STATUS_VOID,
                'deliverable_ir_status' => CaseDeliverable::STATUS_VOID,
            ]);

            AuditLog::recordHuman(
                AuditLog::ACTION_DELIVERABLE_VOIDED,
                $request->case_no
            );
        });
    }

    // -------------------------------------------------------
    // 有効期限切れチェック（Laravelスケジューラから定期実行）
    // -------------------------------------------------------

    /**
     * 有効期限切れのVRを VOID に変更する。
     * → schedule で毎日実行: $schedule->call([CaseDeliverableService::class, 'expireOverdue'])->daily();
     */
    public function expireOverdue(): int
    {
        $expired = CaseDeliverable::where('deliverable_type', CaseDeliverable::TYPE_VR)
            ->where('deliverable_status', CaseDeliverable::STATUS_ISSUED)
            ->where('is_active', true)
            ->where('expires_at', '<', now())
            ->get();

        foreach ($expired as $deliverable) {
            $deliverable->update([
                'deliverable_status' => CaseDeliverable::STATUS_VOID,
                'is_active'          => false,
            ]);

            AuditLog::recordSystem(
                AuditLog::ACTION_DELIVERABLE_EXPIRED,
                $deliverable->case_no,
                ['serial_no' => $deliverable->serial_no]
            );
        }

        return $expired->count();
    }

    // -------------------------------------------------------
    // プライベートヘルパー
    // -------------------------------------------------------

    /** 指定案件・種別のアクティブな結果物を取得 */
    private function getActive(int $certRequestId, string $type): ?CaseDeliverable
    {
        return CaseDeliverable::where('certification_request_id', $certRequestId)
            ->where('deliverable_type', $type)
            ->where('is_active', true)
            ->first();
    }

    /** 採番ルール: VR-2026-00001 / IR-2026-00001 */
    private function generateSerialNo(string $type): string
    {
        $year  = now()->year;
        $count = CaseDeliverable::where('deliverable_type', $type)
            ->where('deliverable_status', CaseDeliverable::STATUS_ISSUED)
            ->whereYear('generated_at', $year)
            ->count() + 1;

        return sprintf('%s-%d-%05d', $type, $year, $count);
    }
}
