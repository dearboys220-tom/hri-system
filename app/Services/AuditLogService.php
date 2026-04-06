<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\StaffActivityLog;

class AuditLogService
{
    /**
     * ============================================================
     * AuditLogService
     *
     * 全業務イベントの監査ログを記録する。
     * AuditLog モデルの recordHuman / recordAi / recordSystem を
     * ラップし、各業務フローから呼び出しやすくする。
     *
     * 使用例:
     *   app(AuditLogService::class)->certRequestCreated($request);
     *   app(AuditLogService::class)->approved($request, $staffId);
     *   app(AuditLogService::class)->aiReviewRun($request, $promptVersion);
     * ============================================================
     */

    // -------------------------------------------------------
    // 会員登録・同意
    // -------------------------------------------------------

    public function signup(int $userId): AuditLog
    {
        return AuditLog::recordHuman(
            AuditLog::ACTION_SIGNUP,
            null,
            ['new' => ['user_id' => $userId]]
        );
    }

    public function consentGranted(int $userId, string $consentType, ?string $caseNo = null): AuditLog
    {
        return AuditLog::recordHuman(
            AuditLog::ACTION_CONSENT_GRANTED,
            $caseNo,
            ['new' => ['user_id' => $userId, 'consent_type' => $consentType]]
        );
    }

    public function consentWithdrawn(int $userId, string $consentType, ?string $caseNo = null): AuditLog
    {
        return AuditLog::recordHuman(
            AuditLog::ACTION_CONSENT_WITHDRAWN,
            $caseNo,
            ['new' => ['user_id' => $userId, 'consent_type' => $consentType]]
        );
    }

    // -------------------------------------------------------
    // 認証申請
    // -------------------------------------------------------

    public function certRequestCreated(\App\Models\CertificationRequest $request): AuditLog
    {
        return AuditLog::recordHuman(
            AuditLog::ACTION_CERT_REQUEST_CREATED,
            $request->case_no,
            ['new' => ['certification_request_id' => $request->id]]
        );
    }

    public function investigationAssigned(\App\Models\CertificationRequest $request, int $investigatorId): AuditLog
    {
        return AuditLog::recordHuman(
            AuditLog::ACTION_INVESTIGATION_ASSIGNED,
            $request->case_no,
            ['new' => ['assigned_investigator' => $investigatorId]]
        );
    }

    public function investigationCreated(\App\Models\CertificationRequest $request): AuditLog
    {
        return AuditLog::recordHuman(
            AuditLog::ACTION_INVESTIGATION_CREATED,
            $request->case_no
        );
    }

    public function investigationUpdated(\App\Models\CertificationRequest $request, array $before, array $after): AuditLog
    {
        return AuditLog::recordHuman(
            AuditLog::ACTION_INVESTIGATION_UPDATED,
            $request->case_no,
            ['old' => $before, 'new' => $after]
        );
    }

    public function policyViolationFlagged(\App\Models\CertificationRequest $request, int $itemId): AuditLog
    {
        return AuditLog::recordHuman(
            AuditLog::ACTION_POLICY_VIOLATION_FLAGGED,
            $request->case_no,
            ['new' => ['investigation_item_id' => $itemId]]
        );
    }

    // -------------------------------------------------------
    // AI審査
    // -------------------------------------------------------

    public function aiReviewRun(\App\Models\CertificationRequest $request, string $promptVersion): AuditLog
    {
        return AuditLog::recordAi(
            AuditLog::ACTION_AI_REVIEW_RUN,
            $request->case_no,
            $promptVersion
        );
    }

    public function aiProposed(\App\Models\CertificationRequest $request, string $decision, string $promptVersion): AuditLog
    {
        return AuditLog::recordAi(
            AuditLog::ACTION_AI_PROPOSED,
            $request->case_no,
            $promptVersion,
            ['new' => ['ai_proposed_decision' => $decision]]
        );
    }

    // -------------------------------------------------------
    // 審査管理部の操作
    // -------------------------------------------------------

    public function humanOverride(
        \App\Models\CertificationRequest $request,
        string $aiDecision,
        string $humanDecision,
        int    $staffId
    ): AuditLog {
        return AuditLog::recordHuman(
            AuditLog::ACTION_HUMAN_OVERRIDE,
            $request->case_no,
            [
                'old' => ['ai_proposed_decision' => $aiDecision],
                'new' => ['human_override_decision' => $humanDecision, 'staff_id' => $staffId],
            ]
        );
    }

    public function approved(\App\Models\CertificationRequest $request, int $staffId): AuditLog
    {
        return AuditLog::recordHuman(
            AuditLog::ACTION_APPROVED,
            $request->case_no,
            ['new' => ['approved_by_user_id' => $staffId]]
        );
    }

    public function conditionalApproved(\App\Models\CertificationRequest $request, int $staffId): AuditLog
    {
        return AuditLog::recordHuman(
            AuditLog::ACTION_CONDITIONAL_APPROVED,
            $request->case_no,
            ['new' => ['approved_by_user_id' => $staffId]]
        );
    }

    public function rejected(\App\Models\CertificationRequest $request, int $staffId, string $reason): AuditLog
    {
        return AuditLog::recordHuman(
            AuditLog::ACTION_REJECTED,
            $request->case_no,
            ['new' => ['rejected_by' => $staffId, 'reason' => $reason]]
        );
    }

    public function humanReviewAssigned(\App\Models\CertificationRequest $request, int $staffId): AuditLog
    {
        return AuditLog::recordHuman(
            AuditLog::ACTION_HUMAN_REVIEW_ASSIGNED,
            $request->case_no,
            ['new' => ['assigned_to' => $staffId]]
        );
    }

    // -------------------------------------------------------
    // 差し戻し
    // -------------------------------------------------------

    public function returnCreated(\App\Models\CertificationRequest $request, int $returnId): AuditLog
    {
        return AuditLog::recordHuman(
            AuditLog::ACTION_RETURN_CREATED,
            $request->case_no,
            ['new' => ['case_return_id' => $returnId]]
        );
    }

    public function returnResolved(\App\Models\CertificationRequest $request, int $staffId): AuditLog
    {
        return AuditLog::recordHuman(
            AuditLog::ACTION_RETURN_RESOLVED,
            $request->case_no,
            ['new' => ['resolved_by' => $staffId]]
        );
    }

    // -------------------------------------------------------
    // 企業閲覧・申請者閲覧
    // -------------------------------------------------------

    public function companyViewed(string $caseNo, int $companyUserId, int $deliverableId): AuditLog
    {
        return AuditLog::recordHuman(
            AuditLog::ACTION_COMPANY_VIEWED,
            $caseNo,
            ['new' => ['company_user_id' => $companyUserId, 'deliverable_id' => $deliverableId]]
        );
    }

    public function applicantViewed(string $caseNo, int $applicantUserId, int $deliverableId): AuditLog
    {
        return AuditLog::recordHuman(
            AuditLog::ACTION_APPLICANT_VIEWED,
            $caseNo,
            ['new' => ['applicant_user_id' => $applicantUserId, 'deliverable_id' => $deliverableId]]
        );
    }

    // -------------------------------------------------------
    // データ管理
    // -------------------------------------------------------

    public function exportRequested(string $exportType, int $staffId): AuditLog
    {
        return AuditLog::recordHuman(
            AuditLog::ACTION_EXPORT_REQUESTED,
            null,
            ['new' => ['export_type' => $exportType, 'requested_by' => $staffId]]
        );
    }

    public function deleteRequested(int $userId): AuditLog
    {
        return AuditLog::recordHuman(
            AuditLog::ACTION_DELETE_REQUESTED,
            null,
            ['new' => ['user_id' => $userId]]
        );
    }

    public function accountSuspended(int $targetUserId, int $staffId): AuditLog
    {
        return AuditLog::recordHuman(
            AuditLog::ACTION_ACCOUNT_SUSPENDED,
            null,
            ['new' => ['target_user_id' => $targetUserId, 'suspended_by' => $staffId]]
        );
    }

    public function superAdminAccess(string $accessedPage): AuditLog
    {
        return AuditLog::recordHuman(
            AuditLog::ACTION_SUPER_ADMIN_ACCESS,
            null,
            ['new' => ['page' => $accessedPage]]
        );
    }
}
