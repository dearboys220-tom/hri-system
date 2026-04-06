<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AiActivityLog;
use App\Models\CertificationRequest;
use App\Models\DataDeletionRequest;
use App\Models\StaffActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class SuperAdminDashboardController extends Controller
{
    public function index(): Response
    {
        $today = now()->startOfDay();
        $monthStart = now()->startOfMonth();

        // -------------------------------------------------------
        // AIコスト・処理件数
        // -------------------------------------------------------
        $aiStats = AiActivityLog::where('created_at', '>=', $monthStart)
            ->selectRaw('
                COUNT(*)                        AS total_count,
                SUM(tokens_total)               AS total_tokens,
                SUM(estimated_cost_idr)         AS total_cost_idr,
                AVG(latency_ms)                 AS avg_latency_ms,
                SUM(CASE WHEN status = "success" THEN 1 ELSE 0 END) AS success_count
            ')
            ->first();

        $successRate = $aiStats->total_count > 0
            ? round($aiStats->success_count / $aiStats->total_count * 100, 1)
            : 0;

        // -------------------------------------------------------
        // 案件ステータス別件数
        // -------------------------------------------------------
        $caseStats = CertificationRequest::selectRaw('current_status, COUNT(*) AS count')
            ->groupBy('current_status')
            ->pluck('count', 'current_status');

        // -------------------------------------------------------
        // スタッフ活動（本日）
        // -------------------------------------------------------
        $staffStats = StaffActivityLog::where('created_at', '>=', $today)
            ->selectRaw('role_type, COUNT(*) AS count')
            ->groupBy('role_type')
            ->pluck('count', 'role_type');

        // -------------------------------------------------------
        // PDP法関連アラート
        // -------------------------------------------------------
        $pdpAlerts = [
            'unresolved_deletions' => DataDeletionRequest::whereIn('status', [
                DataDeletionRequest::STATUS_PENDING,
                DataDeletionRequest::STATUS_UNDER_REVIEW,
            ])->count(),
        ];

        // -------------------------------------------------------
        // 直近7日間のAIコスト推移（グラフ用）
        // -------------------------------------------------------
        $costTrend = AiActivityLog::where('created_at', '>=', now()->subDays(7))
            ->selectRaw('DATE(created_at) AS date, SUM(estimated_cost_idr) AS daily_cost')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // -------------------------------------------------------
        // 今日のAIチャット利用回数
        // -------------------------------------------------------
        $todayChatCount = DB::table('ai_chat_logs')
            ->where('created_at', '>=', $today)
            ->where('message_role', 'user')
            ->count();

        return Inertia::render('SuperAdmin/Dashboard', [
            'aiStats'       => [
                'totalCount'   => $aiStats->total_count ?? 0,
                'totalTokens'  => $aiStats->total_tokens ?? 0,
                'totalCostIdr' => number_format($aiStats->total_cost_idr ?? 0),
                'avgLatencyMs' => round($aiStats->avg_latency_ms ?? 0),
                'successRate'  => $successRate,
            ],
            'caseStats'     => $caseStats,
            'staffStats'    => [
                'investigator' => $staffStats['investigator_user'] ?? 0,
                'admin'        => $staffStats['admin_user'] ?? 0,
                'chatCount'    => $todayChatCount,
            ],
            'pdpAlerts'     => $pdpAlerts,
            'costTrend'     => $costTrend,
        ]);
    }
}