<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\MonthlyAccountingReport;
use App\Models\Invoice;
use App\Models\AuditLog;
use App\Services\ClaudeSubpromptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class AccountingController extends Controller
{
    public function __construct(private ClaudeSubpromptService $claude) {}

    // ── 一覧
    public function index()
    {
        $reports = MonthlyAccountingReport::with(['createdBy', 'approvedBy'])
            ->orderByDesc('report_month')
            ->paginate(12);

        return Inertia::render('Accounting/Index', compact('reports'));
    }

    // ── 新規作成フォーム（月を選んで請求書データを自動集計）
    public function create()
    {
        // 過去12ヶ月のリストを生成
        $months = [];
        for ($i = 0; $i < 12; $i++) {
            $m = Carbon::now('Asia/Jakarta')->subMonths($i)->format('Y-m');
            $months[] = $m;
        }

        // 既存レポートの月を除外
        $existing = MonthlyAccountingReport::pluck('report_month')->toArray();
        $months   = array_filter($months, fn($m) => !in_array($m, $existing));

        return Inertia::render('Accounting/Create', [
            'available_months' => array_values($months),
        ]);
    }

    // ── 月データ集計（Ajax）
    public function fetchMonthData(Request $request)
    {
        $request->validate(['month' => 'required|string|regex:/^\d{4}-\d{2}$/']);

        $month      = $request->month;
        $periodFrom = Carbon::parse($month . '-01')->startOfMonth()->toDateString();
        $periodTo   = Carbon::parse($month . '-01')->endOfMonth()->toDateString();

        // 対象月の請求書を集計
        $paidInvoices = Invoice::whereBetween('paid_at', [$periodFrom . ' 00:00:00', $periodTo . ' 23:59:59'])
            ->where('status', 'paid')
            ->get(['id', 'invoice_no', 'client_name', 'final_amount', 'paid_at', 'payment_method']);

        $pendingInvoices = Invoice::whereBetween('created_at', [$periodFrom . ' 00:00:00', $periodTo . ' 23:59:59'])
            ->whereIn('status', ['sent', 'overdue'])
            ->get(['id', 'invoice_no', 'client_name', 'final_amount', 'due_date', 'status']);

        return response()->json([
            'period_from'           => $periodFrom,
            'period_to'             => $periodTo,
            'paid_invoices'         => $paidInvoices,
            'pending_invoices'      => $pendingInvoices,
            'total_revenue'         => $paidInvoices->sum('final_amount'),
            'total_pending'         => $pendingInvoices->sum('final_amount'),
            'paid_invoice_count'    => $paidInvoices->count(),
            'pending_invoice_count' => $pendingInvoices->count(),
        ]);
    }

    // ── 月次レポート保存（draft）
    public function store(Request $request)
    {
        $request->validate([
            'report_month'  => 'required|string|regex:/^\d{4}-\d{2}$/',
            'period_from'   => 'required|date',
            'period_to'     => 'required|date',
            'expense_items' => 'nullable|array',
            'expense_items.*.name'       => 'required|string',
            'expense_items.*.amount'     => 'required|integer|min:0',
            'expense_items.*.receipt_no' => 'nullable|string',
            'notes'         => 'nullable|string',
        ]);

        // 重複チェック
        if (MonthlyAccountingReport::where('report_month', $request->report_month)->exists()) {
            return redirect()->back()->withErrors(['error' => 'Laporan bulan ini sudah ada.']);
        }

        $expenseItems  = $request->expense_items ?? [];
        $totalExpenses = collect($expenseItems)->sum('amount');

        // 請求書データ再集計
        $paidInvoices = Invoice::whereBetween('paid_at', [
            $request->period_from . ' 00:00:00',
            $request->period_to . ' 23:59:59'
        ])->where('status', 'paid')->get();

        $pendingInvoices = Invoice::whereBetween('created_at', [
            $request->period_from . ' 00:00:00',
            $request->period_to . ' 23:59:59'
        ])->whereIn('status', ['sent', 'overdue'])->get();

        $report = MonthlyAccountingReport::create([
            'report_month'          => $request->report_month,
            'period_from'           => $request->period_from,
            'period_to'             => $request->period_to,
            'status'                => MonthlyAccountingReport::STATUS_DRAFT,
            'total_revenue'         => $paidInvoices->sum('final_amount'),
            'total_pending'         => $pendingInvoices->sum('final_amount'),
            'paid_invoice_count'    => $paidInvoices->count(),
            'pending_invoice_count' => $pendingInvoices->count(),
            'included_invoice_ids'  => $paidInvoices->pluck('id')->toArray(),
            'expense_items'         => $expenseItems,
            'total_expenses'        => $totalExpenses,
            'notes'                 => $request->notes,
            'created_by_user_id'    => Auth::id(),
        ]);

        AuditLog::create([
            'user_id'     => Auth::id(),
            'action_type' => 'ACCOUNTING_REPORT_CREATED',
            'target_type' => 'monthly_accounting_reports',
            'target_id'   => $report->id,
            'notes'       => 'Laporan akuntansi bulanan dibuat: ' . $request->report_month,
        ]);

        return redirect()->route('accounting.show', $report->id)
            ->with('success', 'Laporan berhasil dibuat.');
    }

    // ── 詳細
    public function show(MonthlyAccountingReport $report)
    {
        $report->load(['createdBy', 'approvedBy']);

        // 対象請求書の詳細を取得
        $invoices = Invoice::whereIn('id', $report->included_invoice_ids ?? [])
            ->get(['id', 'invoice_no', 'client_name', 'final_amount', 'paid_at', 'payment_method']);

        return Inertia::render('Accounting/Show', compact('report', 'invoices'));
    }

    // ── H-1 AI整理実行
    public function organizeWithAi(MonthlyAccountingReport $report)
    {
        $input = [
            'report_month'          => $report->report_month,
            'period_from'           => $report->period_from,
            'period_to'             => $report->period_to,
            'total_revenue_idr'     => $report->total_revenue,
            'total_pending_idr'     => $report->total_pending,
            'total_expenses_idr'    => $report->total_expenses,
            'paid_invoice_count'    => $report->paid_invoice_count,
            'pending_invoice_count' => $report->pending_invoice_count,
            'expense_items'         => $report->expense_items ?? [],
            'notes'                 => $report->notes,
        ];

        $result = $this->claude->organizeMonthlyAccounting($input);

        if (isset($result['error'])) {
            return redirect()->back()->withErrors(['error' => 'AI gagal memproses. Coba lagi.']);
        }

        $report->update([
            'status'                 => MonthlyAccountingReport::STATUS_AI_ORGANIZED,
            'ai_summary'             => $result['summary'] ? json_encode($result['summary'], JSON_UNESCAPED_UNICODE) : null,
            'ai_anomaly_notes'       => $result['anomaly_notes'] ?? null,
            'ai_draft_cover_letter'  => $result['draft_cover_letter'] ?? null,
            'ai_checklist'           => $result['checklist'] ? json_encode($result['checklist'], JSON_UNESCAPED_UNICODE) : null,
            'ai_risk_flags'          => $result['risk_flags'] ?? [],
            'ai_generated_at'        => now(),
        ]);

        AuditLog::create([
            'user_id'     => Auth::id(),
            'action_type' => 'ACCOUNTING_AI_ORGANIZED',
            'target_type' => 'monthly_accounting_reports',
            'target_id'   => $report->id,
            'notes'       => 'AI (H-1) mengorganisir laporan akuntansi: ' . $report->report_month,
        ]);

        return redirect()->back()->with('success', 'AI berhasil mengorganisir laporan.');
    }

    // ── 承認申請
    public function submitForApproval(MonthlyAccountingReport $report)
    {
        $report->update(['status' => MonthlyAccountingReport::STATUS_PENDING_APPROVAL]);

        AuditLog::create([
            'user_id'     => Auth::id(),
            'action_type' => 'ACCOUNTING_SUBMITTED',
            'target_type' => 'monthly_accounting_reports',
            'target_id'   => $report->id,
            'notes'       => 'Laporan diajukan untuk persetujuan',
        ]);

        return redirect()->back()->with('success', 'Laporan diajukan untuk persetujuan.');
    }

    // ── 承認
    public function approve(MonthlyAccountingReport $report)
    {
        $report->update([
            'status'             => MonthlyAccountingReport::STATUS_APPROVED,
            'approved_by_user_id' => Auth::id(),
            'approved_at'        => now(),
        ]);

        AuditLog::create([
            'user_id'     => Auth::id(),
            'action_type' => 'ACCOUNTING_APPROVED',
            'target_type' => 'monthly_accounting_reports',
            'target_id'   => $report->id,
            'notes'       => 'Laporan akuntansi disetujui',
        ]);

        return redirect()->back()->with('success', 'Laporan disetujui.');
    }

    // ── 送付済み記録
    public function markSent(Request $request, MonthlyAccountingReport $report)
    {
        $request->validate([
            'sent_to_email' => 'required|email',
        ]);

        $report->update([
            'status'        => MonthlyAccountingReport::STATUS_SENT,
            'sent_to_email' => $request->sent_to_email,
            'sent_at'       => now(),
        ]);

        AuditLog::create([
            'user_id'     => Auth::id(),
            'action_type' => 'ACCOUNTING_SENT',
            'target_type' => 'monthly_accounting_reports',
            'target_id'   => $report->id,
            'notes'       => '送付先: ' . $request->sent_to_email,
        ]);

        return redirect()->back()->with('success', 'Laporan ditandai sebagai terkirim ke akuntan.');
    }

    // ── 受領確認
    public function acknowledge(MonthlyAccountingReport $report)
    {
        $report->update([
            'status'          => MonthlyAccountingReport::STATUS_ACKNOWLEDGED,
            'acknowledged_at' => now(),
        ]);

        AuditLog::create([
            'user_id'     => Auth::id(),
            'action_type' => 'ACCOUNTING_ACKNOWLEDGED',
            'target_type' => 'monthly_accounting_reports',
            'target_id'   => $report->id,
            'notes'       => 'Akuntan eksternal telah menerima laporan',
        ]);

        return redirect()->back()->with('success', 'Penerimaan oleh akuntan dikonfirmasi.');
    }
}