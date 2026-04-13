<?php

namespace App\Http\Controllers;

use App\Models\EmployeeAbsenceRequest;
use App\Models\StaffAvailability;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

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
                'start_date'      => $r->start_date,
                'end_date'        => $r->end_date,
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
            'absence_type' => 'required|in:SICK,PERSONAL,ANNUAL_LEAVE,OTHER',
            'start_date'   => 'required|date|after_or_equal:today',
            'end_date'     => 'required|date|after_or_equal:start_date',
            'reason'       => 'required|string|max:1000',
            'document_url' => 'nullable|url|max:500',
        ]);

        $user = Auth::user();

        $overlap = EmployeeAbsenceRequest::where('staff_user_id', $user->id)
            ->whereIn('approval_status', ['PENDING', 'APPROVED'])
            ->where(function ($q) use ($request) {
                $q->whereBetween('start_date', [$request->start_date, $request->end_date])
                  ->orWhereBetween('end_date', [$request->start_date, $request->end_date]);
            })->exists();

        if ($overlap) {
            return back()->withErrors(['start_date' => 'Sudah ada pengajuan izin pada periode tersebut.']);
        }

        $absenceRequest = EmployeeAbsenceRequest::create([
            'staff_user_id'   => $user->id,
            'absence_type'    => $request->absence_type,
            'start_date'      => $request->start_date,
            'end_date'        => $request->end_date,
            'reason'          => $request->reason,
            'document_url'    => $request->document_url,
            'approval_status' => 'PENDING',
        ]);

        AuditLog::recordHuman(
            'ABSENCE_REQUESTED',
            null,
            ['new' => [
                'absence_type' => $request->absence_type,
                'start_date'   => $request->start_date,
                'end_date'     => $request->end_date,
            ]]
        );

        return back()->with('success', 'Pengajuan izin berhasil dikirim. Menunggu persetujuan manajer.');
    }

    public function index()
    {
        $requests = EmployeeAbsenceRequest::with(['staffUser:id,name,role_type'])
            ->orderByRaw("FIELD(approval_status, 'PENDING', 'APPROVED', 'REJECTED')")
            ->orderBy('start_date', 'asc')
            ->get()
            ->map(fn($r) => [
                'id'              => $r->id,
                'staff_name'      => optional($r->staffUser)->name ?? '—',
                'role_type'       => optional($r->staffUser)->role_type ?? '—',
                'absence_type'    => $r->absence_type,
                'start_date'      => $r->start_date,
                'end_date'        => $r->end_date,
                'reason'          => $r->reason,
                'document_url'    => $r->document_url,
                'approval_status' => $r->approval_status,
                'approved_by'     => $r->approvedBy?->name ?? null,
                'manager_note'    => $r->manager_note,
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
            'manager_note'        => $request->manager_note,
        ]);

        $availability = StaffAvailability::where('staff_user_id', $absence->staff_user_id)->first();
        if ($availability) {
            $availability->update([
                'availability_status' => StaffAvailability::STATUS_ON_LEAVE,
                'absence_until'       => $absence->end_date,
            ]);
        }

        AuditLog::recordHuman(
            'ABSENCE_APPROVED',
            null,
            ['new' => [
                'absence_id'     => $absence->id,
                'approval_status'=> 'APPROVED',
                'absence_until'  => $absence->end_date,
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
            'manager_note'        => $request->manager_note,
        ]);

        AuditLog::recordHuman(
            'ABSENCE_APPROVED',
            null,
            ['new' => [
                'absence_id'      => $absence->id,
                'approval_status' => 'REJECTED',
                'manager_note'    => $request->manager_note,
            ]]
        );

        return back()->with('success', 'Pengajuan izin telah ditolak.');
    }
}