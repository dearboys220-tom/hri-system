<?php

namespace App\Http\Controllers;

use App\Models\PayrollRecord;
use App\Models\SalaryCalculation;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PayrollRecordController extends Controller
{
    // ─────────────────────────────────────────────
    // 支払い記録一覧
    // ─────────────────────────────────────────────
    public function index()
    {
        $payrolls = PayrollRecord::with(['staff', 'salaryCalculation', 'processedBy'])
            ->orderByDesc('created_at')
            ->paginate(20);

        // 承認済み給与計算のうち、まだ支払い記録がないもの
        $pendingCalculations = SalaryCalculation::with('staff')
            ->where('calculation_status', 'APPROVED')
            ->whereDoesntHave('payrollRecord')
            ->orderByDesc('created_at')
            ->get();

        return Inertia::render('Manager/Payroll/Index', [
            'payrolls'            => $payrolls,
            'pendingCalculations' => $pendingCalculations,
        ]);
    }

    // ─────────────────────────────────────────────
    // 支払い記録を作成（SCHEDULED）
    // ─────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'salary_calculation_id' => 'required|exists:salary_calculations,id',
            'bank_account_name'     => 'required|string|max:255',
            'bank_name'             => 'required|string|max:255',
            'bank_account_no'       => 'required|string|max:50',
            'payment_method'        => 'required|in:BANK_TRANSFER,CASH,QRIS,OTHER',
        ]);

        $calculation = SalaryCalculation::with('staff')->findOrFail($request->salary_calculation_id);

        // 重複チェック
        $exists = PayrollRecord::where('salary_calculation_id', $calculation->id)->exists();
        if ($exists) {
            return back()->with('error', 'Catatan pembayaran untuk data gaji ini sudah ada.');
        }

        $payroll = PayrollRecord::create([
            'salary_calculation_id' => $calculation->id,
            'staff_user_id'         => $calculation->staff_user_id,
            'payment_month'         => $calculation->calculation_month,
            'bank_account_name'     => $request->bank_account_name,
            'bank_name'             => $request->bank_name,
            'bank_account_no'       => $request->bank_account_no,
            'paid_amount'           => $calculation->net_salary,
            'payment_method'        => $request->payment_method,
            'payment_status'        => 'SCHEDULED',
        ]);

        AuditLog::create([
            'action_type'  => 'PAYROLL_PROCESSED',
            'performed_by' => Auth::id(),
            'target_type'  => 'payroll_records',
            'target_id'    => $payroll->id,
            'new_value'    => json_encode([
                'staff_user_id'  => $calculation->staff_user_id,
                'payment_month'  => $calculation->calculation_month,
                'paid_amount'    => $calculation->net_salary,
                'payment_status' => 'SCHEDULED',
            ]),
            'notes' => 'Catatan pembayaran dibuat untuk: ' . $calculation->staff->name . ' (' . $calculation->calculation_month . ')',
        ]);

        return back()->with('success', 'Catatan pembayaran berhasil dibuat untuk ' . $calculation->staff->name . '.');
    }

    // ─────────────────────────────────────────────
    // 送金処理済みにマーク（PROCESSED）
    // ─────────────────────────────────────────────
    public function markProcessed(Request $request, PayrollRecord $payroll)
    {
        $request->validate([
            'payment_reference_no' => 'nullable|string|max:100',
        ]);

        $payroll->update([
            'payment_status'       => 'PROCESSED',
            'payment_reference_no' => $request->payment_reference_no,
            'processed_at'         => now(),
            'processed_by_user_id' => Auth::id(),
        ]);

        AuditLog::create([
            'action_type'  => 'PAYROLL_PROCESSED',
            'performed_by' => Auth::id(),
            'target_type'  => 'payroll_records',
            'target_id'    => $payroll->id,
            'new_value'    => json_encode([
                'payment_status'       => 'PROCESSED',
                'payment_reference_no' => $request->payment_reference_no,
            ]),
            'notes' => 'Pembayaran diproses untuk: ' . $payroll->staff->name . ' (' . $payroll->payment_month . ')',
        ]);

        return back()->with('success', 'Status pembayaran diperbarui menjadi Diproses.');
    }

    // ─────────────────────────────────────────────
    // 受取確認済みにマーク（CONFIRMED）
    // ─────────────────────────────────────────────
    public function markConfirmed(PayrollRecord $payroll)
    {
        $payroll->update([
            'payment_status' => 'CONFIRMED',
            'confirmed_at'   => now(),
        ]);

        AuditLog::create([
            'action_type'  => 'PAYROLL_PROCESSED',
            'performed_by' => Auth::id(),
            'target_type'  => 'payroll_records',
            'target_id'    => $payroll->id,
            'new_value'    => json_encode(['payment_status' => 'CONFIRMED']),
            'notes' => 'Pembayaran dikonfirmasi diterima: ' . $payroll->staff->name . ' (' . $payroll->payment_month . ')',
        ]);

        return back()->with('success', 'Pembayaran dikonfirmasi sebagai diterima.');
    }

    // ─────────────────────────────────────────────
    // 失敗にマーク（FAILED）
    // ─────────────────────────────────────────────
    public function markFailed(PayrollRecord $payroll)
    {
        $payroll->update([
            'payment_status' => 'FAILED',
        ]);

        AuditLog::create([
            'action_type'  => 'PAYROLL_PROCESSED',
            'performed_by' => Auth::id(),
            'target_type'  => 'payroll_records',
            'target_id'    => $payroll->id,
            'new_value'    => json_encode(['payment_status' => 'FAILED']),
            'notes' => 'Pembayaran gagal: ' . $payroll->staff->name . ' (' . $payroll->payment_month . ')',
        ]);

        return back()->with('success', 'Status pembayaran diperbarui menjadi Gagal.');
    }
}