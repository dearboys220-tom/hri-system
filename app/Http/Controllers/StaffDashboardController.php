<?php

namespace App\Http\Controllers;

use App\Models\AiTaskAssignment;
use App\Models\EmployeeAbsenceRequest;
use App\Models\StaffAvailability;
use App\Models\StaffEvaluation;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class StaffDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 自分のタスク一覧（employee_user_id ✅）
        $myTasks = AiTaskAssignment::with(['taskOrder'])
            ->where('employee_user_id', $user->id)
            ->orderByRaw("FIELD(task_status,
                'ASSIGNED','IN_PROGRESS','DELAYED','ESCALATED','COMPLETED','FAILED')")
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(fn($a) => [
                'id'           => $a->id,
                'title'        => optional($a->taskOrder)->instruction_title ?? '—',
                'description'  => optional($a->taskOrder)->instruction_body ?? '—',
                'priority'     => optional($a->taskOrder)->priority_level ?? 'NORMAL',
                'due_date'     => $a->due_at?->format('Y-m-d'),
                'task_status'  => $a->task_status,
                'started_at'   => $a->started_at?->format('Y-m-d H:i'),
                'completed_at' => $a->completed_at?->format('Y-m-d H:i'),
            ]);

        // 自分の欠勤申請履歴（staff_user_id / absence_date_from / absence_date_to ✅）
        $myAbsences = EmployeeAbsenceRequest::where('staff_user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['id', 'absence_type', 'absence_date_from', 'absence_date_to', 'approval_status']);

        // 自分の稼働状況（staff_user_id ✅）
        $availability = StaffAvailability::where('staff_user_id', $user->id)
            ->first(['availability_status', 'active_task_count', 'max_concurrent_tasks']);

        // 自分の査定履歴（staff_user_id ✅）
        $myEvaluations = StaffEvaluation::where('staff_user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get([
                'id',
                'evaluation_period_from',
                'evaluation_period_to',
                'evaluation_type',
                'ai_performance_band',
                'human_final_band',
                'ai_score',
            ]);

        return Inertia::render('Staff/Dashboard', [
            'user'          => $user->only(['id', 'name', 'role_type']),
            'myTasks'       => $myTasks,
            'myAbsences'    => $myAbsences,
            'availability'  => $availability,
            'myEvaluations' => $myEvaluations,
        ]);
    }
}