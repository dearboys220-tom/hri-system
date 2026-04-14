<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\MarketingCampaign;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class MarketingCampaignController extends Controller
{
    // ── 一覧 ──────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $search = $request->query('search', '');
        $status = $request->query('status', 'all');

        $query = MarketingCampaign::with(['assignedUser:id,name'])
            ->orderByDesc('created_at');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('campaign_name', 'like', "%{$search}%")
                  ->orWhere('campaign_no', 'like', "%{$search}%");
            });
        }
        if ($status !== 'all') {
            $query->where('campaign_status', $status);
        }

        $campaigns = $query->paginate(20)->through(fn($c) => [
            'id'              => $c->id,
            'campaign_no'     => $c->campaign_no,
            'campaign_name'   => $c->campaign_name,
            'campaign_type'   => $c->campaign_type,
            'campaign_status' => $c->campaign_status,
            'budget'          => $c->budget,
            'assigned_name'   => optional($c->assignedUser)->name ?? '-',
            'started_at'      => optional($c->started_at)->format('d/m/Y'),
            'ended_at'        => optional($c->ended_at)->format('d/m/Y'),
        ]);

        $stats = [
            'planning'  => MarketingCampaign::where('campaign_status', 'PLANNING')->count(),
            'active'    => MarketingCampaign::where('campaign_status', 'ACTIVE')->count(),
            'on_hold'   => MarketingCampaign::where('campaign_status', 'ON_HOLD')->count(),
            'completed' => MarketingCampaign::where('campaign_status', 'COMPLETED')->count(),
        ];

        $marketingUsers = User::where('role_type', 'marketing_user')
            ->where('status', 'active')
            ->get(['id', 'name']);

        return Inertia::render('Marketing/Campaigns/Index', [
            'campaigns'      => $campaigns,
            'stats'          => $stats,
            'marketingUsers' => $marketingUsers,
            'search'         => $search,
            'statusFilter'   => $status,
        ]);
    }

    // ── 新規作成 ──────────────────────────────────────────────────
    public function store(Request $request)
    {
        $validated = $request->validate([
            'campaign_name'      => 'required|string|max:255',
            'campaign_type'      => 'required|in:MARKET_RESEARCH,ADVERTISING,SALES_MANAGEMENT,EVENT,OTHER',
            'target_description' => 'nullable|string',
            'assigned_user_id'   => 'nullable|exists:users,id',
            'budget'             => 'nullable|numeric|min:0',
            'started_at'         => 'nullable|date',
            'ended_at'           => 'nullable|date',
        ]);

        $year  = Carbon::now()->format('Y');
        $count = MarketingCampaign::whereYear('created_at', $year)->count() + 1;
        $validated['campaign_no'] = sprintf('MKT-%s-%04d', $year, $count);

        $campaign = MarketingCampaign::create($validated);

        AuditLog::recordHuman('TASK_ORDER_CREATED', null, [
            'new' => ['campaign_no' => $campaign->campaign_no, 'campaign_name' => $campaign->campaign_name],
        ]);

        return back()->with('success', 'Kampanye berhasil dibuat.');
    }

    // ── ステータス更新 ────────────────────────────────────────────
    public function updateStatus(Request $request, int $id)
    {
        $validated = $request->validate([
            'campaign_status' => 'required|in:PLANNING,ACTIVE,ON_HOLD,COMPLETED,CANCELLED',
            'result_report'   => 'nullable|string',
            'ended_at'        => 'nullable|date',
        ]);

        $campaign = MarketingCampaign::findOrFail($id);
        $campaign->update($validated);

        AuditLog::recordHuman('TASK_ORDER_APPROVED', null, [
            'new' => ['campaign_no' => $campaign->campaign_no, 'campaign_status' => $validated['campaign_status']],
        ]);

        return back()->with('success', 'Status kampanye berhasil diperbarui.');
    }
}