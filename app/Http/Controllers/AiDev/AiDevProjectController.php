<?php

namespace App\Http\Controllers\AiDev;

use App\Http\Controllers\Controller;
use App\Models\AiDevProject;
use App\Models\CompanyProfile;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class AiDevProjectController extends Controller
{
    // ── 一覧 ──────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $search = $request->query('search', '');
        $status = $request->query('status', 'all');

        $query = AiDevProject::with(['clientCompany:id,company_name', 'leadUser:id,name'])
            ->orderByDesc('created_at');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('project_name', 'like', "%{$search}%")
                  ->orWhere('project_no', 'like', "%{$search}%");
            });
        }
        if ($status !== 'all') {
            $query->where('project_status', $status);
        }

        $projects = $query->paginate(20)->through(fn($p) => [
            'id'              => $p->id,
            'project_no'      => $p->project_no,
            'project_name'    => $p->project_name,
            'project_type'    => $p->project_type,
            'project_status'  => $p->project_status,
            'billing_status'  => $p->billing_status,
            'contract_amount' => $p->contract_amount,
            'client_name'     => optional($p->clientCompany)->company_name ?? '-',
            'lead_name'       => optional($p->leadUser)->name ?? '-',
            'started_at'      => optional($p->started_at)->format('d/m/Y'),
            'delivery_due_at' => optional($p->delivery_due_at)->format('d/m/Y'),
            'is_overdue'      => $p->isOverdue(),
        ]);

        $stats = [
            'proposal'    => AiDevProject::where('project_status', 'PROPOSAL')->count(),
            'in_progress' => AiDevProject::where('project_status', 'IN_PROGRESS')->count(),
            'testing'     => AiDevProject::where('project_status', 'TESTING')->count(),
            'delivered'   => AiDevProject::where('project_status', 'DELIVERED')->count(),
            'overdue'     => AiDevProject::all()->filter(fn($p) => $p->isOverdue())->count(),
        ];

        $aiDevUsers = User::where('role_type', 'ai_dev_user')
            ->where('status', 'active')
            ->get(['id', 'name']);

        $companies = CompanyProfile::where('company_verification_status', 'verified')
            ->get(['id', 'company_name']);

        return Inertia::render('AiDev/Projects/Index', [
            'projects'     => $projects,
            'stats'        => $stats,
            'aiDevUsers'   => $aiDevUsers,
            'companies'    => $companies,
            'search'       => $search,
            'statusFilter' => $status,
        ]);
    }

    // ── 新規作成 ──────────────────────────────────────────────────
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_name'      => 'required|string|max:255',
            'project_type'      => 'required|in:CONSULTATION,DESIGN,DEVELOPMENT,DELIVERY,MAINTENANCE,OTHER',
            'project_description'=> 'nullable|string',
            'client_company_id' => 'nullable|exists:company_profiles,id',
            'lead_user_id'      => 'nullable|exists:users,id',
            'contract_amount'   => 'nullable|numeric|min:0',
            'started_at'        => 'nullable|date',
            'delivery_due_at'   => 'nullable|date',
        ]);

        $year  = Carbon::now()->format('Y');
        $count = AiDevProject::whereYear('created_at', $year)->count() + 1;
        $validated['project_no'] = sprintf('AID-%s-%04d', $year, $count);

        $project = AiDevProject::create($validated);

        AuditLog::recordHuman('TASK_ORDER_CREATED', null, [
            'new' => ['project_no' => $project->project_no, 'project_name' => $project->project_name],
        ]);

        return back()->with('success', 'Proyek berhasil dibuat.');
    }

    // ── ステータス更新 ────────────────────────────────────────────
    public function updateStatus(Request $request, int $id)
    {
        $validated = $request->validate([
            'project_status'  => 'required|in:PROPOSAL,CONTRACTED,IN_PROGRESS,TESTING,DELIVERED,MAINTENANCE,COMPLETED,CANCELLED',
            'billing_status'  => 'nullable|in:UNBILLED,BILLED,PAID',
            'delivered_at'    => 'nullable|date',
        ]);

        $project = AiDevProject::findOrFail($id);
        $project->update($validated);

        AuditLog::recordHuman('TASK_ORDER_APPROVED', null, [
            'new' => ['project_no' => $project->project_no, 'project_status' => $validated['project_status']],
        ]);

        return back()->with('success', 'Status proyek berhasil diperbarui.');
    }
}