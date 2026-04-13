<?php

namespace App\Http\Controllers;

use App\Models\EmployeeAbsenceRequest;
use App\Models\StaffAvailability;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class AbsenceRequestController extends Controller
{
    public function create()
    {
        $user = Auth::user();

        $myRequests = EmployeeAbsenceRequest::where('staff_user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(fn($r) => [
                'id'              => $r->id,
                'absence_type'    => $r->absence_type,
                'start_date'      => $r->absence_date_from,
                'end_date'        => $r->absence_date_to,
                'absence_days'    => $r->absence_days,
                'reason'          => $r->reason,
                'approval_status' => $r->approval_status,
                'created_at'      => $r->created_at->format('Y-m-d H:i'),
            ]);

        return Inertia::render('Staff/Absence/Create', [
            'myRequests' => $myRequests,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'absence_type' => 'required|in:SICK,PERSONAL,ANNUAL_LEAVE,EMERGENCY,OTHER',
            'start_date'   => 'required|date|after_or_equal:today',
            'end_date'     => 'required|date|after_or_equal:start_date',
            'reason'       => 'required|string|max:1000',
            'document_url' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();

        // 重複申請チェック
        $overlap = EmployeeAbsenceRequest::where('staff_user_id', $user->id)
            ->whereIn('approval_status', ['PENDING', 'APPROVED'])
            ->where(function ($q) use ($request) {
                $q->whereBetween('absence_date_from', [$request->start_date, $request->end_date])
                  ->orWhereBetween('absence_date_to', [$request->start_date, $request->end_date]);
            })->exists();

        if ($overlap) {
            return back()->withErrors(['start_date' => 'Sudah ada pengajuan izin pada periode tersebut.']);
        }

        // 欠勤日数を計算
        $absenceDays = Carbon::parse($request->start_date)
            ->diffInDays(Carbon::parse($request->end_date)) + 1;

        $absenceRequest = EmployeeAbsenceRequest::create([
            'staff_user_id'      => $user->id,
            'absence_type'       => $request->absence_type,
            'absence_date_from'  => $request->start_date,
            'absence_date_to'    => $request->end_date,
            'absence_days'       => $absenceDays,
            'reason'             => $request->reason,
            'supporting_doc_path'=> $request->document_url,
            'approval_status'    => 'PENDING',
        ]);

        AuditLog::recordHuman(
            'ABSENCE_REQUESTED',
            null,
            ['new' => [
                'absence_type'      => $request->absence_type,
                'absence_date_from' => $request->start_date,
                'absence_date_to'   => $request->end_date,
                'absence_days'      => $absenceDays,
            ]]
        );

        return back()->with('success', 'Pengajuan izin berhasil dikirim. Menunggu persetujuan manajer.');
    }

    public function index()
    {
        $requests = EmployeeAbsenceRequest::with(['staffUser:id,name,role_type'])
            ->orderByRaw("FIELD(approval_status, 'PENDING', 'APPROVED', 'REJECTED')")
            ->orderBy('absence_date_from', 'asc')
            ->get()
            ->map(fn($r) => [
                'id'              => $r->id,
                'staff_name'      => optional($r->staffUser)->name ?? '—',
                'role_type'       => optional($r->staffUser)->role_type ?? '—',
                'absence_type'    => $r->absence_type,
                'start_date'      => $r->absence_date_from,
                'end_date'        => $r->absence_date_to,
                'absence_days'    => $r->absence_days,
                'reason'          => $r->reason,
                'document_url'    => $r->supporting_doc_path,
                'approval_status' => $r->approval_status,
                'rejection_reason'=> $r->rejection_reason,
                'created_at'      => $r->created_at->format('Y-m-d H:i'),
            ]);

        return Inertia::render('Manager/AbsenceRequests', [
            'requests' => $requests,
        ]);
    }

    public function approve(Request $request, $id)
    {
        $request->validate([
            'manager_note' => 'nullable|string|max:500',
        ]);

        $manager = Auth::user();
        $absence = EmployeeAbsenceRequest::findOrFail($id);

        if ($absence->approval_status !== 'PENDING') {
            return back()->withErrors(['error' => 'Status pengajuan sudah diproses.']);
        }

        $absence->update([
            'approval_status'     => 'APPROVED',
            'approved_by_user_id' => $manager->id,
            'approved_at'         => now(),
        ]);

        // staff_availability を ON_LEAVE に更新
        $availability = StaffAvailability::where('staff_user_id', $absence->staff_user_id)->first();
        if ($availability) {
            $availability->update([
                'availability_status' => StaffAvailability::STATUS_ON_LEAVE,
                'absence_until'       => $absence->absence_date_to,
            ]);
        }

        AuditLog::recordHuman(
            'ABSENCE_APPROVED',
            null,
            ['new' => [
                'absence_id'      => $absence->id,
                'approval_status' => 'APPROVED',
                'absence_until'   => $absence->absence_date_to,
            ]]
        );

        return back()->with('success', 'Pengajuan izin telah disetujui.');
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'manager_note' => 'required|string|max:500',
        ]);

        $manager = Auth::user();
        $absence = EmployeeAbsenceRequest::findOrFail($id);

        if ($absence->approval_status !== 'PENDING') {
            return back()->withErrors(['error' => 'Status pengajuan sudah diproses.']);
        }

        $absence->update([
            'approval_status'     => 'REJECTED',
            'approved_by_user_id' => $manager->id,
            'approved_at'         => now(),
            'rejection_reason'    => $request->manager_note,
        ]);

        AuditLog::recordHuman(
            'ABSENCE_APPROVED',
            null,
            ['new' => [
                'absence_id'       => $absence->id,
                'approval_status'  => 'REJECTED',
                'rejection_reason' => $request->manager_note,
            ]]
        );

        return back()->with('success', 'Pengajuan izin telah ditolak.');
    }
}