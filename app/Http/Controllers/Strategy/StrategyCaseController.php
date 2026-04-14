<?php

namespace App\Http\Controllers\Strategy;

use App\Http\Controllers\Controller;
use App\Models\StrategyCase;
use App\Models\CompanyProfile;
use App\Models\User;
use App\Models\AuditLog;
use App\Services\ClaudeSubpromptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class StrategyCaseController extends Controller
{
    public function __construct(
        private ClaudeSubpromptService $subprompt
    ) {}

    // ── 一覧 ──────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $search = $request->query('search', '');
        $status = $request->query('status', 'all');

        $query = StrategyCase::with(['clientCompany:id,company_name', 'assignedUser:id,name'])
            ->orderByDesc('created_at');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('case_title', 'like', "%{$search}%")
                  ->orWhere('case_no', 'like', "%{$search}%");
            });
        }
        if ($status !== 'all') {
            $query->where('case_status', $status);
        }

        $cases = $query->paginate(20)->through(fn($c) => [
            'id'              => $c->id,
            'case_no'         => $c->case_no,
            'case_title'      => $c->case_title,
            'case_type'       => $c->case_type,
            'case_status'     => $c->case_status,
            'risk_level'      => $c->risk_level,
            'billing_status'  => $c->billing_status,
            'client_name'     => optional($c->clientCompany)->company_name ?? '-',
            'assigned_name'   => optional($c->assignedUser)->name ?? '-',
            'started_at'      => optional($c->started_at)->format('d/m/Y'),
            'resolved_at'     => optional($c->resolved_at)->format('d/m/Y'),
            'requires_lawyer' => $c->requires_registered_lawyer,
        ]);

        $stats = [
            'open'           => StrategyCase::where('case_status', 'OPEN')->count(),
            'in_progress'    => StrategyCase::where('case_status', 'IN_PROGRESS')->count(),
            'pending_lawyer' => StrategyCase::where('case_status', 'PENDING_LAWYER')->count(),
            'resolved'       => StrategyCase::where('case_status', 'RESOLVED')->count(),
            'high_risk'      => StrategyCase::where('risk_level', 'HIGH')->count(),
        ];

        $strategyUsers = User::where('role_type', 'strategy_user')
            ->where('status', 'active')
            ->get(['id', 'name']);

        $companies = CompanyProfile::where('company_verification_status', 'verified')
            ->get(['id', 'company_name']);

        return Inertia::render('Strategy/Cases/Index', [
            'cases'         => $cases,
            'stats'         => $stats,
            'strategyUsers' => $strategyUsers,
            'companies'     => $companies,
            'search'        => $search,
            'statusFilter'  => $status,
        ]);
    }

    // ── 新規作成 ──────────────────────────────────────────────────
    public function store(Request $request)
    {
        $validated = $request->validate([
            'case_title'                 => 'required|string|max:255',
            'case_type'                  => 'required|in:LABOR_LAW,CONTRACT,COMPLIANCE,BANKRUPTCY,DISPUTE,OTHER',
            'case_description'           => 'nullable|string',
            'client_company_id'          => 'nullable|exists:company_profiles,id',
            'assigned_user_id'           => 'nullable|exists:users,id',
            'risk_level'                 => 'required|in:HIGH,MEDIUM,LOW',
            'requires_registered_lawyer' => 'boolean',
            'started_at'                 => 'nullable|date',
            'fee_amount'                 => 'nullable|numeric|min:0',
        ]);

        // case_no 採番: ST-YYYY-NNNN
        $year  = Carbon::now()->format('Y');
        $count = StrategyCase::whereYear('created_at', $year)->count() + 1;
        $validated['case_no'] = sprintf('ST-%s-%04d', $year, $count);

        $case = StrategyCase::create($validated);

        AuditLog::recordHuman('TASK_ORDER_CREATED', null, [
            'new' => ['case_no' => $case->case_no, 'case_title' => $case->case_title],
        ]);

        return back()->with('success', 'Kasus berhasil dibuat.');
    }

    // ── ステータス更新 ────────────────────────────────────────────
    public function updateStatus(Request $request, int $id)
    {
        $validated = $request->validate([
            'case_status'        => 'required|in:OPEN,IN_PROGRESS,PENDING_LAWYER,RESOLVED,CLOSED,ESCALATED',
            'resolution_summary' => 'nullable|string',
            'billing_status'     => 'nullable|in:UNBILLED,BILLED,PAID',
            'resolved_at'        => 'nullable|date',
        ]);

        $case = StrategyCase::findOrFail($id);
        $case->update($validated);

        AuditLog::recordHuman('TASK_ORDER_APPROVED', null, [
            'new' => ['case_no' => $case->case_no, 'case_status' => $validated['case_status']],
        ]);

        return back()->with('success', 'Status kasus berhasil diperbarui.');
    }

    // ── AI リスクサマリー生成 ─────────────────────────────────────
    public function generateAiSummary(Request $request, int $id)
    {
        $case = StrategyCase::with('clientCompany')->findOrFail($id);

        // G-1 ではなく内部サマリー用に ClaudeSubpromptService の K-3 を流用
        // （法的断定禁止・参考情報のみ）
        $input = [
            'case_no'          => $case->case_no,
            'case_type'        => $case->case_type,
            'case_title'       => $case->case_title,
            'case_description' => $case->case_description,
            'risk_level'       => $case->risk_level,
            'client_name'      => optional($case->clientCompany)->company_name,
            'instruction'      => 'リスクサマリーを参考情報として生成。法的断定表現は使用禁止。「〜の可能性があります」「〜のリスクが考えられます」表現のみ使用。',
        ];

        $result = $this->subprompt->runK3($input, 'strategy_user');

        if (empty($result['_error'])) {
            $summary = $result['summary_for_approver'] ?? json_encode($result, JSON_UNESCAPED_UNICODE);
            $case->update(['ai_risk_summary' => $summary]);

            AuditLog::recordAi('AI_REVIEW_RUN', null, 'K-3', [
                'new' => ['case_no' => $case->case_no, 'type' => 'risk_summary'],
            ]);
        }

        return back()->with('success', 'Ringkasan risiko AI berhasil dibuat.');
    }
}