<?php

namespace App\Services;

use App\Models\DocumentNumberSequence;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

/**
 * NumberingService
 * Section 28・32 準拠。全採番は本サービス経由。
 * カラム名は実DBに合わせて使用（member_id / serial_no 等）。
 */
class NumberingService
{
    // ─────────────────────────────────────────────
    // 個人会員番号: HRI-MEM-I-YYYY-NNNNNN
    // applicant_profiles.member_id に保存
    // ─────────────────────────────────────────────
    public function issueMemberNoForApplicant(): string
    {
        $year   = Carbon::now()->format('Y');
        $seq    = $this->nextSequence('MEM_I', $year);
        $number = sprintf('HRI-MEM-I-%s-%06d', $year, $seq);

        $this->recordAuditLog('NUMBER_ISSUED', 'MEM_I', $number);

        return $number;
    }

    // ─────────────────────────────────────────────
    // 企業会員番号: HRI-MEM-C-YYYY-NNNNNN
    // company_profiles.member_id に保存
    // ─────────────────────────────────────────────
    public function issueMemberNoForCompany(): string
    {
        $year   = Carbon::now()->format('Y');
        $seq    = $this->nextSequence('MEM_C', $year);
        $number = sprintf('HRI-MEM-C-%s-%06d', $year, $seq);

        $this->recordAuditLog('NUMBER_ISSUED', 'MEM_C', $number);

        return $number;
    }

    // ─────────────────────────────────────────────
    // VRシリアル: HRI-VR-YYYYMM-NNNNNN-V
    // case_deliverables 等に保存
    // ─────────────────────────────────────────────
    public function issueVrSerial(string $versionCode = 'A'): string
    {
        $yearMonth = Carbon::now()->format('Ym');
        $seq       = $this->nextSequence('VR', $yearMonth);
        $number    = sprintf('HRI-VR-%s-%06d-%s', $yearMonth, $seq, strtoupper($versionCode));

        $this->recordAuditLog('NUMBER_ISSUED', 'VR', $number);

        return $number;
    }

    // ─────────────────────────────────────────────
    // 完了レポート番号: HRI-RPT-DEPT-YYYY-NNNN
    // task_completion_reports.report_no に保存
    // ─────────────────────────────────────────────
    public function issueReportNo(string $deptCode = 'ADMIN'): string
    {
        $year   = Carbon::now()->format('Y');
        $seq    = $this->nextSequence('RPT', $year, $deptCode);
        $number = sprintf('HRI-RPT-%s-%s-%04d', strtoupper($deptCode), $year, $seq);

        $this->recordAuditLog('NUMBER_ISSUED', 'RPT', $number, $deptCode);

        return $number;
    }

    // ─────────────────────────────────────────────
    // 警告書番号: HRI-WRN-HR-YYYY-NNNN
    // ─────────────────────────────────────────────
    public function issueWarningNo(string $deptCode = 'HR'): string
    {
        $year   = Carbon::now()->format('Y');
        $seq    = $this->nextSequence('WRN', $year, $deptCode);
        $number = sprintf('HRI-WRN-%s-%s-%04d', strtoupper($deptCode), $year, $seq);

        $this->recordAuditLog('NUMBER_ISSUED', 'WRN', $number, $deptCode);

        return $number;
    }

    // ─────────────────────────────────────────────
    // 見積書番号: HRI-EST-DEPT-YYYY-NNNN
    // ─────────────────────────────────────────────
    public function issueEstimateNo(string $deptCode = 'SALES'): string
    {
        $year   = Carbon::now()->format('Y');
        $seq    = $this->nextSequence('EST', $year, $deptCode);
        $number = sprintf('HRI-EST-%s-%s-%04d', strtoupper($deptCode), $year, $seq);

        $this->recordAuditLog('NUMBER_ISSUED', 'EST', $number, $deptCode);

        return $number;
    }

    // ─────────────────────────────────────────────
    // 受注確認書番号: HRI-ORD-DEPT-YYYY-NNNN
    // ─────────────────────────────────────────────
    public function issueOrderNo(string $deptCode = 'SALES'): string
    {
        $year   = Carbon::now()->format('Y');
        $seq    = $this->nextSequence('ORD', $year, $deptCode);
        $number = sprintf('HRI-ORD-%s-%s-%04d', strtoupper($deptCode), $year, $seq);

        $this->recordAuditLog('NUMBER_ISSUED', 'ORD', $number, $deptCode);

        return $number;
    }

    // ─────────────────────────────────────────────
    // 請求書番号: HRI-INV-DEPT-YYYY-NNNN
    // ─────────────────────────────────────────────
    public function issueInvoiceNo(string $deptCode = 'SALES'): string
    {
        $year   = Carbon::now()->format('Y');
        $seq    = $this->nextSequence('INV', $year, $deptCode);
        $number = sprintf('HRI-INV-%s-%s-%04d', strtoupper($deptCode), $year, $seq);

        $this->recordAuditLog('NUMBER_ISSUED', 'INV', $number, $deptCode);

        return $number;
    }

    // ─────────────────────────────────────────────
    // 問い合わせ番号: HRI-QRY-YYYYMMDD-NNNN
    // ─────────────────────────────────────────────
    public function issueInquiryNo(): string
    {
        $date   = Carbon::now()->format('Ymd');
        $seq    = $this->nextSequence('QRY', $date);
        $number = sprintf('HRI-QRY-%s-%04d', $date, $seq);

        $this->recordAuditLog('NUMBER_ISSUED', 'QRY', $number);

        return $number;
    }

    // ─────────────────────────────────────────────
    // 共通：次の連番を取得（行ロックで重複防止）
    // ─────────────────────────────────────────────
    private function nextSequence(
        string  $numberType,
        string  $periodKey,
        ?string $deptCode = null
    ): int {
        return DB::transaction(function () use ($numberType, $periodKey, $deptCode) {

            $row = DocumentNumberSequence::lockForUpdate()
                ->where('number_type', $numberType)
                ->where('period_key',  $periodKey)
                ->where('dept_code',   $deptCode)
                ->first();

            if ($row) {
                $row->increment('last_sequence');
                return $row->fresh()->last_sequence;
            }

            // 初回は新規作成
            DocumentNumberSequence::create([
                'number_type'   => $numberType,
                'period_key'    => $periodKey,
                'last_sequence' => 1,
                'dept_code'     => $deptCode,
            ]);

            return 1;
        });
    }

    // ─────────────────────────────────────────────
    // audit_logs に NUMBER_ISSUED を記録
    // 実DBカラム名: actor_type='system'固定・serial_noに番号保存
    // ─────────────────────────────────────────────
    private function recordAuditLog(
        string  $actionType,
        string  $numberType,
        string  $issuedNumber,
        ?string $deptCode = null
    ): void {
        try {
            AuditLog::recordSystem(
                $actionType,
                null,
                [
                    'serial_no' => $issuedNumber,
                    'new'       => [
                        'number_type'   => $numberType,
                        'issued_number' => $issuedNumber,
                        'dept_code'     => $deptCode,
                        'issued_at'     => now()->toDateTimeString(),
                    ],
                ]
            );
        } catch (\Throwable $e) {
            \Log::error('NumberingService audit_log error: ' . $e->getMessage());
        }
    }
}