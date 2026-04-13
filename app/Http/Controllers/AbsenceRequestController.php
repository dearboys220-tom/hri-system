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
    // ─────────────────────────────────────────────
    // スタッフ側：申請フォーム表示
    // ─────────────────────────────────────────────
    public function create()
    {
        $user = Auth::user();

        // 自分の申請履歴（直近10件）
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

    // ─────────────────────────────────────────────
    // スタッフ側：申請を送信
    // ─────────────────────────────────────────────
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

        // 同じ期間に重複申請がないか確認
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

        // 監査ログ
        AuditLog::create([
            'user_id'      => $user->id,
            'action_type'  => 'ABSENCE_REQUESTED',
            'target_table' => 'employee_absence_requests',
            'target_id'    => $absenceRequest->id,
            'new_values'   => json_encode($absenceRequest->toArray()),
            'memo'         => "Pengajuan izin: {$request->absence_type} ({$request->start_date} ~ {$request->end_date})",
        ]);

        return back()->with('success', 'Pengajuan izin berhasil dikirim. Menunggu persetujuan manajer.');
    }

    // ─────────────────────────────────────────────
    // マネージャー側：申請一覧
    // ─────────────────────────────────────────────
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

    // ─────────────────────────────────────────────
    // マネージャー側：承認
    // ─────────────────────────────────────────────
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
            'approval_status' => 'APPROVED',
            'approved_by_user_id' => $manager->id,
            'approved_at'     => now(),
            'manager_note'    => $request->manager_note,
        ]);

        // ── staff_availability を ON_LEAVE に自動更新 ──
        $availability = StaffAvailability::where('staff_user_id', $absence->staff_user_id)->first();
        if ($availability) {
            $availability->update([
                'availability_status' => 'ON_LEAVE',
                'absence_until'       => $absence->end_date,
            ]);
        }

        // 監査ログ
        AuditLog::create([
            'user_id'      => $manager->id,
            'action_type'  => 'ABSENCE_APPROVED',
            'target_table' => 'employee_absence_requests',
            'target_id'    => $absence->id,
            'new_values'   => json_encode(['approval_status' => 'APPROVED', 'absence_until' => $absence->end_date]),
            'memo'         => "Izin disetujui oleh {$manager->name}. Berlaku: {$absence->start_date} ~ {$absence->end_date}",
        ]);

        return back()->with('success', 'Pengajuan izin telah disetujui.');
    }

    // ─────────────────────────────────────────────
    // マネージャー側：却下
    // ─────────────────────────────────────────────
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

        // 監査ログ
        AuditLog::create([
            'user_id'      => $manager->id,
            'action_type'  => 'ABSENCE_APPROVED',  // audit_logsのaction_typeはAPPROVEDで統一
            'target_table' => 'employee_absence_requests',
            'target_id'    => $absence->id,
            'new_values'   => json_encode(['approval_status' => 'REJECTED']),
            'memo'         => "Izin ditolak oleh {$manager->name}. Alasan: {$request->manager_note}",
        ]);

        return back()->with('success', 'Pengajuan izin telah ditolak.');
    }
}