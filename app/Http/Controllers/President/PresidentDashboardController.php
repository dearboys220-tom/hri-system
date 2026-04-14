<?php

namespace App\Http\Controllers\President;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\StaffEvaluation;
use App\Models\SalaryCalculation;
use App\Models\PayrollRecord;
use App\Models\StaffAvailability;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PresidentDashboardController extends Controller
{
    public function index()
    {
        // スタッフ統計
        $totalStaff = User::whereIn('role_type', [
            'investigator_user', 'admin_user', 'em_staff',
            'strategy_user', 'ai_dev_user', 'marketing_user',
            'local_manager',
        ])->where('status', 'active')->count();

        // 稼働状況
        $availabilityStats = StaffAvailability::selectRaw('
            availability_status, COUNT(*) as count
        ')->groupBy('availability_status')->pluck('count', 'availability_status');

        // 査定サマリー（直近3ヶ月・確定済み）
        $evaluationSummary = StaffEvaluation::whereNotNull('human_final_band')
            ->where('created_at', '>=', now()->subMonths(3))
            ->selectRaw('human_final_band, COUNT(*) as count')
            ->groupBy('human_final_band')
            ->pluck('count', 'human_final_band');

        // 給与計算DRAFTで承認待ち
        $draftSalaries = SalaryCalculation::with('staff')
            ->where('calculation_status', 'DRAFT')
            ->orderByDesc('created_at')
            ->get();

        // 承認済み給与計算のうち支払い記録未作成
        $pendingSalaries = SalaryCalculation::with('staff')
            ->where('calculation_status', 'APPROVED')
            ->whereDoesntHave('payrollRecord')
            ->orderByDesc('approved_at')
            ->get();

        // 支払い記録（SCHEDULED / PROCESSED）
        $activePayrolls = PayrollRecord::with('staff')
            ->whereIn('payment_status', ['SCHEDULED', 'PROCESSED'])
            ->orderByDesc('created_at')
            ->get();

        // 部署別スタッフ数
        $staffByDept = User::whereIn('role_type', [
                'investigator_user', 'admin_user', 'em_staff',
                'strategy_user', 'ai_dev_user', 'marketing_user',
            ])
            ->where('status', 'active')
            ->selectRaw('role_type, COUNT(*) as count')
            ->groupBy('role_type')
            ->pluck('count', 'role_type');

        // 直近の監査ログ（10件）
        $recentLogs = AuditLog::with('user')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get(['id', 'action_type', 'user_id', 'actor_type', 'created_at']);
        
        // チャット用スタッフ一覧（全社内スタッフ）
        $allStaff = User::whereIn('role_type', [
                'investigator_user', 'admin_user', 'em_staff',
                'strategy_user', 'ai_dev_user', 'marketing_user',
                'local_manager',
            ])
            ->where('status', 'active')
            ->get(['id', 'name', 'role_type']);

        return Inertia::render('President/Dashboard', [
            'totalStaff'        => $totalStaff,
            'availabilityStats' => $availabilityStats,
            'evaluationSummary' => $evaluationSummary,
            'draftSalaries'     => $draftSalaries,
            'pendingSalaries'   => $pendingSalaries,
            'activePayrolls'    => $activePayrolls,
            'staffByDept'       => $staffByDept,
            'recentLogs'        => $recentLogs,
            'allStaff'          => $allStaff,
        ]);
    }

    public function approveSalary(SalaryCalculation $calculation)
    {
        if ($calculation->calculation_status !== 'DRAFT') {
            return back()->with('error', 'Data gaji ini sudah diproses.');
        }

        $calculation->update([
            'calculation_status'  => 'APPROVED',
            'approved_by_user_id' => Auth::id(),
            'approved_at'         => now(),
        ]);

        AuditLog::recordHuman('SALARY_APPROVED', null, [
            'new' => [
                'calculation_id'     => $calculation->id,
                'staff_name'         => $calculation->staff->name,
                'calculation_month'  => $calculation->calculation_month,
                'calculation_status' => 'APPROVED',
            ],
        ]);

        return back()->with('success', 'Gaji ' . $calculation->staff->name . ' (' . $calculation->calculation_month . ') berhasil disetujui.');
    }
}