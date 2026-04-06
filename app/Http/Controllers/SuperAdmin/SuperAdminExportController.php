<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AiActivityLog;
use App\Models\AiChatLog;
use App\Models\AiDataTransferLog;
use App\Models\AuditLog;
use App\Models\ConsentRecord;
use App\Models\PersonalDataAccessLog;
use App\Models\StaffActivityLog;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Inertia\Inertia;

class SuperAdminExportController extends Controller
{
    public function __construct(private AuditLogService $auditLogService) {}

    // -------------------------------------------------------
    // エクスポート画面
    // -------------------------------------------------------
    public function index(): \Inertia\Response
    {
        return Inertia::render('SuperAdmin/Export');
    }

    // -------------------------------------------------------
    // CSV ダウンロード
    // -------------------------------------------------------
    public function download(Request $request): Response|\Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'export_type' => 'required|in:ai_logs,ai_chat_logs,staff_logs,data_access_logs,ai_transfer_logs,audit_logs,consent_records',
            'date_from'   => 'nullable|date',
            'date_to'     => 'nullable|date|after_or_equal:date_from',
        ]);

        // エクスポート操作を監査ログに記録
        $this->auditLogService->exportRequested($request->export_type, auth()->id());

        $csv      = $this->buildCsv($request->export_type, $request->date_from, $request->date_to);
        $filename = $request->export_type . '_' . now()->format('Ymd_His') . '.csv';

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    // -------------------------------------------------------
    // CSV 生成（種別ごと）
    // -------------------------------------------------------
    private function buildCsv(string $type, ?string $dateFrom, ?string $dateTo): string
    {
        $output = match ($type) {
            'ai_logs'          => $this->exportAiLogs($dateFrom, $dateTo),
            'ai_chat_logs'     => $this->exportAiChatLogs($dateFrom, $dateTo),
            'staff_logs'       => $this->exportStaffLogs($dateFrom, $dateTo),
            'data_access_logs' => $this->exportDataAccessLogs($dateFrom, $dateTo),
            'ai_transfer_logs' => $this->exportAiTransferLogs($dateFrom, $dateTo),
            'audit_logs'       => $this->exportAuditLogs($dateFrom, $dateTo),
            'consent_records'  => $this->exportConsentRecords($dateFrom, $dateTo),
            default            => '',
        };

        // BOM付きUTF-8（Excelで文字化けしない）
        return "\xEF\xBB\xBF" . $output;
    }

    // -------------------------------------------------------
    // AI活動ログ CSV
    // -------------------------------------------------------
    private function exportAiLogs(?string $from, ?string $to): string
    {
        $headers = ['ID', '実行日時', '種別', '関連モデル', '関連ID', 'AIモデル',
                    '入力トークン', '出力トークン', '合計トークン', 'コスト(IDR)',
                    '応答時間(ms)', 'ステータス', 'AI判定結果'];

        $rows = AiActivityLog::when($from, fn($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to,   fn($q) => $q->whereDate('created_at', '<=', $to))
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($r) => [
                $r->id, $r->created_at, $r->log_type, $r->related_type, $r->related_id,
                $r->model_name, $r->tokens_input, $r->tokens_output, $r->tokens_total,
                $r->estimated_cost_idr, $r->latency_ms, $r->status, $r->final_decision,
            ]);

        return $this->toCsv($headers, $rows);
    }

    // -------------------------------------------------------
    // AIチャットログ CSV
    // -------------------------------------------------------
    private function exportAiChatLogs(?string $from, ?string $to): string
    {
        $headers = ['ID', '日時', 'ユーザーID', 'ロール', 'セッションID',
                    '発言者', 'トークン数', 'PIIフラグ'];

        $rows = AiChatLog::when($from, fn($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to,   fn($q) => $q->whereDate('created_at', '<=', $to))
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($r) => [
                $r->id, $r->created_at, $r->user_id, $r->role_type,
                $r->session_id, $r->message_role,
                $r->tokens_used, $r->contains_pii_flag ? 'あり' : 'なし',
                // ⚠️ message_content はエクスポートしない（個人情報保護）
            ]);

        return $this->toCsv($headers, $rows);
    }

    // -------------------------------------------------------
    // スタッフ操作ログ CSV
    // -------------------------------------------------------
    private function exportStaffLogs(?string $from, ?string $to): string
    {
        $headers = ['ID', '操作日時', 'スタッフID', 'ロール', 'アクション',
                    '対象モデル', '対象ID', '説明', 'IPアドレス', '案件番号'];

        $rows = StaffActivityLog::when($from, fn($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to,   fn($q) => $q->whereDate('created_at', '<=', $to))
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($r) => [
                $r->id, $r->created_at, $r->user_id, $r->role_type,
                $r->action, $r->target_type, $r->target_id,
                $r->description, $r->ip_address, $r->case_no,
                // ⚠️ before_value / after_value は含めない（個人情報が含まれる可能性）
            ]);

        return $this->toCsv($headers, $rows);
    }

    // -------------------------------------------------------
    // 個人データアクセスログ CSV
    // -------------------------------------------------------
    private function exportDataAccessLogs(?string $from, ?string $to): string
    {
        $headers = ['ID', 'アクセス日時', 'アクセス者ID', 'ロール',
                    'データ種別', 'アクション', 'アクセス理由', 'IPアドレス', '案件番号'];

        $rows = PersonalDataAccessLog::when($from, fn($q) => $q->whereDate('accessed_at', '>=', $from))
            ->when($to,   fn($q) => $q->whereDate('accessed_at', '<=', $to))
            ->orderByDesc('accessed_at')
            ->get()
            ->map(fn($r) => [
                $r->id, $r->accessed_at, $r->accessor_user_id, $r->accessor_role,
                $r->data_type, $r->action, $r->access_reason,
                $r->ip_address, $r->case_no,
                // ⚠️ target_user_id / target_member_id は含めない（個人情報保護）
            ]);

        return $this->toCsv($headers, $rows);
    }

    // -------------------------------------------------------
    // AIデータ送信ログ CSV
    // -------------------------------------------------------
    private function exportAiTransferLogs(?string $from, ?string $to): string
    {
        $headers = ['ID', '送信日時', '送信目的', 'AIモデル', 'プロバイダー',
                    '送信カテゴリ', '匿名化済み', '法的根拠'];

        $rows = AiDataTransferLog::when($from, fn($q) => $q->whereDate('transferred_at', '>=', $from))
            ->when($to,   fn($q) => $q->whereDate('transferred_at', '<=', $to))
            ->orderByDesc('transferred_at')
            ->get()
            ->map(fn($r) => [
                $r->id, $r->transferred_at, $r->transfer_purpose, $r->ai_model,
                $r->ai_provider, implode(',', $r->data_categories_sent ?? []),
                $r->is_anonymized ? '済' : '未', $r->legal_basis,
            ]);

        return $this->toCsv($headers, $rows);
    }

    // -------------------------------------------------------
    // 監査ログ CSV
    // -------------------------------------------------------
    private function exportAuditLogs(?string $from, ?string $to): string
    {
        $headers = ['ID', '操作日時', '操作者ID', 'アクター種別',
                    'アクション種別', '案件番号', 'IPアドレス'];

        $rows = AuditLog::when($from, fn($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to,   fn($q) => $q->whereDate('created_at', '<=', $to))
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($r) => [
                $r->id, $r->created_at, $r->user_id, $r->actor_type,
                $r->action_type, $r->case_no, $r->ip_address,
            ]);

        return $this->toCsv($headers, $rows);
    }

    // -------------------------------------------------------
    // 同意記録 CSV
    // -------------------------------------------------------
    private function exportConsentRecords(?string $from, ?string $to): string
    {
        $headers = ['ID', 'ユーザーID', '同意種別', '同意バージョン',
                    '同意', '同意日時', '撤回日時', '取得チャネル'];

        $rows = ConsentRecord::when($from, fn($q) => $q->whereDate('consented_at', '>=', $from))
            ->when($to,   fn($q) => $q->whereDate('consented_at', '<=', $to))
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($r) => [
                $r->id, $r->user_id, $r->consent_type, $r->consent_version,
                $r->consented ? '同意' : '拒否', $r->consented_at,
                $r->withdrawn_at ?? '撤回なし', $r->source_channel,
            ]);

        return $this->toCsv($headers, $rows);
    }

    // -------------------------------------------------------
    // CSV 文字列生成ヘルパー
    // -------------------------------------------------------
    private function toCsv(array $headers, $rows): string
    {
        $lines = [];
        $lines[] = implode(',', array_map([$this, 'escapeCsv'], $headers));

        foreach ($rows as $row) {
            $lines[] = implode(',', array_map([$this, 'escapeCsv'], (array) $row));
        }

        return implode("\n", $lines);
    }

    private function escapeCsv(mixed $value): string
    {
        $value = (string) ($value ?? '');
        if (Str::contains($value, [',', '"', "\n"])) {
            $value = '"' . str_replace('"', '""', $value) . '"';
        }
        return $value;
    }
}