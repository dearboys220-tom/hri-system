<?php

namespace App\Http\Controllers;

use App\Models\AiEmployeeReport;
use App\Models\AiTaskAssignment;
use App\Models\StaffAvailability;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class EmployeeReportController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'task_assignment_id'     => 'required|integer|exists:ai_task_assignments,id',
            'report_body'            => 'required|string|max:3000',
            'evidence_attached_flag' => 'boolean',
        ]);

        $user       = Auth::user();
        $assignment = AiTaskAssignment::where('employee_user_id', $user->id)
                        ->findOrFail($request->task_assignment_id);

        if (in_array($assignment->task_status, [
            AiTaskAssignment::STATUS_COMPLETED,
            AiTaskAssignment::STATUS_FAILED,
        ])) {
            return back()->withErrors(['error' => 'Tugas sudah selesai. Laporan tidak dapat dikirim.']);
        }

        $report = AiEmployeeReport::create([
            'task_assignment_id'     => $assignment->id,
            'employee_user_id'       => $user->id,
            'report_body'            => $request->report_body,
            'evidence_attached_flag' => $request->boolean('evidence_attached_flag'),
            'inconsistency_flag'     => false,
            'reported_at'            => now(),
        ]);

        $assignment->update([
            'task_status'  => AiTaskAssignment::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);

        $avail = StaffAvailability::where('staff_user_id', $user->id)->first();
        if ($avail) {
            $avail->decrementTaskCount();
        }

        AuditLog::recordHuman(
            'TASK_REPORT_SUBMITTED',
            null,
            ['new' => [
                'report_id'              => $report->id,
                'task_assignment_id'     => $assignment->id,
                'evidence_attached_flag' => $report->evidence_attached_flag,
            ]]
        );

        return back()->with('success', 'Laporan berhasil dikirim. Tugas ditandai selesai.');
    }

    public function index()
    {
        $reports = AiEmployeeReport::with([
                'employee:id,name,role_type',
                'taskAssignment.taskOrder',
            ])
            ->orderBy('reported_at', 'desc')
            ->paginate(30)
            ->through(fn($r) => [
                'id'                     => $r->id,
                'staff_name'             => optional($r->employee)->name ?? '—',
                'role_type'              => optional($r->employee)->role_type ?? '—',
                'task_title'             => optional(optional($r->taskAssignment)->taskOrder)->instruction_title ?? '—',
                'task_assignment_id'     => $r->task_assignment_id,
                'report_body'            => $r->report_body,
                'ai_summary'             => $r->ai_summary,
                'evidence_attached_flag' => $r->evidence_attached_flag,
                'inconsistency_flag'     => $r->inconsistency_flag,
                'reported_at'            => $r->reported_at?->format('Y-m-d H:i'),
            ]);

        return Inertia::render('Manager/Reports/Index', [
            'reports' => $reports,
        ]);
    }

    public function flagInconsistency(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $manager = Auth::user();
        $report  = AiEmployeeReport::findOrFail($id);

        $report->update(['inconsistency_flag' => true]);

        AuditLog::recordHuman(
            'TASK_REPORT_SUBMITTED',
            null,
            ['new' => [
                'report_id'          => $report->id,
                'inconsistency_flag' => true,
                'reason'             => $request->reason,
            ]]
        );

        return back()->with('success', 'Flag ketidaksesuaian telah ditandai.');
    }
}