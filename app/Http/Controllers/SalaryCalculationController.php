<?php

namespace App\Http\Controllers;

use App\Models\SalaryCalculation;
use App\Models\StaffEvaluation;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;

class SalaryCalculationController extends Controller
{
    public function index()
    {
        $calculations = SalaryCalculation::with(['staff', 'approvedBy'])
            ->orderByDesc('created_at')
            ->paginate(20);

        $staffList = User::whereIn('role_type', [
                'investigator_user', 'admin_user', 'em_staff',
                'strategy_user', 'ai_dev_user', 'marketing_user',
            ])
            ->where('status', 'active')
            ->get(['id', 'name', 'role_type']);

        return Inertia::render('Manager/Salary/Index', [
            'calculations' => $calculations,
            'staffList'    => $staffList,
        ]);
    }

    public function generate(Request $request)
    {
        $request->validate([
            'staff_user_id'     => 'required|exists:users,id',
            'calculation_month' => 'required|string|regex:/^\d{4}-\d{2}$/',
            'base_salary'       => 'required|numeric|min:0',
        ]);

        $staffUserId = $request->staff_user_id;
        $calcMonth   = $request->calculation_month;
        $baseSalary  = $request->base_salary;

        // 重複チェック
        $exists = SalaryCalculation::where('staff_user_id', $staffUserId)
            ->where('calculation_month', $calcMonth)
            ->exists();
        if ($exists) {
            return back()->with('error', 'Data gaji bulan ' . $calcMonth . ' untuk staf ini sudah ada.');
        }

        // 期間設定
        $fromDate = $calcMonth . '-01';
        $toDate   = date('Y-m-t', strtotime($fromDate));

        // 出勤日数
        $attendanceDays = DB::table('em_attendance')
            ->where('em_employee_id', $staffUserId)
            ->whereBetween('date', [$fromDate, $toDate])
            ->count();

        // 欠勤日数（承認済み）
        $absentDays = DB::table('employee_absence_requests')
            ->where('staff_user_id', $staffUserId)
            ->where('approval_status', 'APPROVED')
            ->whereBetween('absence_from', [$fromDate, $toDate])
            ->count();

        // タスク完了率
        $taskStats = DB::table('ai_task_assignments')
            ->where('assigned_user_id', $staffUserId)
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN task_status = "COMPLETED" THEN 1 ELSE 0 END) as completed
            ')
            ->first();
        $taskTotal      = $taskStats->total ?? 0;
        $taskCompleted  = $taskStats->completed ?? 0;
        $completionRate = $taskTotal > 0 ? round(($taskCompleted / $taskTotal) * 100, 1) : null;

        // 確定済み査定バンド取得
        $evaluation = StaffEvaluation::where('staff_user_id', $staffUserId)
            ->where('evaluation_period_from', '>=', $fromDate)
            ->where('evaluation_period_to', '<=', $toDate)
            ->whereNotNull('human_final_band')
            ->latest()
            ->first();
        $performanceBand = $evaluation?->human_final_band;

        $staffUser = User::find($staffUserId);

        // Claude API呼び出し
        $prompt = $this->buildSalaryPrompt([
            'staff_name'       => $staffUser->name,
            'role_type'        => $staffUser->role_type,
            'calc_month'       => $calcMonth,
            'base_salary'      => $baseSalary,
            'attendance_days'  => $attendanceDays,
            'absent_days'      => $absentDays,
            'completion_rate'  => $completionRate,
            'performance_band' => $performanceBand,
        ]);

        try {
            $response = Http::withHeaders([
                'x-api-key'         => config('services.claude.api_key'),
                'anthropic-version' => '2023-06-01',
                'Content-Type'      => 'application/json',
            ])->timeout(30)->post('https://api.anthropic.com/v1/messages', [
                'model'      => 'claude-sonnet-4-20250514',
                'max_tokens' => 1000,
                'messages'   => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

            if (!$response->successful()) {
                return back()->with('error', 'Claude API error: ' . $response->status());
            }

            $body       = $response->json();
            $rawContent = $body['content'][0]['text'] ?? '{}';
            if (preg_match('/\{.*\}/s', $rawContent, $matches)) {
                $rawContent = $matches[0];
            }
            $result = json_decode($rawContent, true) ?? [];

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghubungi API: ' . $e->getMessage());
        }

        $deductions  = $result['deductions']  ?? 0;
        $overtimePay = $result['overtime_pay'] ?? 0;
        $allowances  = $result['allowances']   ?? 0;
        $grossSalary = $baseSalary + $overtimePay + $allowances;
        $netSalary   = $grossSalary - $deductions;
        $notes       = $result['ai_calculation_notes'] ?? '';

        $calculation = SalaryCalculation::create([
            'staff_user_id'         => $staffUserId,
            'calculation_month'     => $calcMonth,
            'base_salary'           => $baseSalary,
            'attendance_days'       => $attendanceDays,
            'absent_days'           => $absentDays,
            'task_completion_rate'  => $completionRate,
            'performance_band'      => $performanceBand,
            'performance_adjustment'=> 0,
            'deductions'            => $deductions,
            'overtime_pay'          => $overtimePay,
            'allowances'            => $allowances,
            'gross_salary'          => $grossSalary,
            'net_salary'            => $netSalary,
            'calculation_status'    => 'DRAFT',
            'ai_calculation_notes'  => $notes,
        ]);

        AuditLog::recordHuman('SALARY_CALCULATED', null, [
            'new' => [
                'calculation_id'    => $calculation->id,
                'staff_user_id'     => $staffUserId,
                'staff_name'        => $staffUser->name,
                'calculation_month' => $calcMonth,
                'net_salary'        => $netSalary,
            ],
        ]);

        return back()->with('success', 'Draft perhitungan gaji untuk ' . $staffUser->name . ' (' . $calcMonth . ') berhasil dibuat.');
    }

    public function approve(Request $request, SalaryCalculation $calculation)
    {
        $request->validate([
            'performance_adjustment' => 'nullable|numeric',
            'deductions'             => 'nullable|numeric|min:0',
            'overtime_pay'           => 'nullable|numeric|min:0',
            'allowances'             => 'nullable|numeric|min:0',
        ]);

        $performanceAdj = $request->performance_adjustment ?? $calculation->performance_adjustment;
        $deductions     = $request->deductions   ?? $calculation->deductions;
        $overtimePay    = $request->overtime_pay ?? $calculation->overtime_pay;
        $allowances     = $request->allowances   ?? $calculation->allowances;
        $grossSalary    = $calculation->base_salary + $performanceAdj + $overtimePay + $allowances;
        $netSalary      = $grossSalary - $deductions;

        $calculation->update([
            'performance_adjustment' => $performanceAdj,
            'deductions'             => $deductions,
            'overtime_pay'           => $overtimePay,
            'allowances'             => $allowances,
            'gross_salary'           => $grossSalary,
            'net_salary'             => $netSalary,
            'calculation_status'     => 'APPROVED',
            'approved_by_user_id'    => Auth::id(),
            'approved_at'            => now(),
        ]);

        AuditLog::recordHuman('SALARY_APPROVED', null, [
            'new' => [
                'calculation_id'     => $calculation->id,
                'staff_name'         => $calculation->staff->name,
                'calculation_month'  => $calculation->calculation_month,
                'net_salary'         => $netSalary,
                'calculation_status' => 'APPROVED',
            ],
        ]);

        return back()->with('success', 'Perhitungan gaji berhasil disetujui. Gaji bersih: Rp ' . number_format($netSalary, 0, ',', '.'));
    }

    private function buildSalaryPrompt(array $data): string
    {
        $band = $data['performance_band'] ?? 'belum ada';
        $rate = $data['completion_rate']  ?? 'belum ada data';

        return <<<PROMPT
Anda adalah AI asisten perhitungan gaji untuk sistem HRI.
Berdasarkan data staf berikut, hitung estimasi komponen gaji dan kembalikan dalam format JSON saja.
Jangan sertakan teks penjelasan, pendahuluan, atau tanda backtick. Kembalikan JSON saja.

[Aturan Perhitungan]
- Potongan absen: gaji_pokok / 22 × jumlah_hari_absen
- Lembur: 0 (tidak ada data lembur, isi 0 kecuali ada indikasi)
- Tunjangan: 0 (isi 0 kecuali ada kebijakan khusus)
- Pajak PPh21 tidak dihitung di sini (diselesaikan oleh akuntan eksternal)
- Jangan tentukan performance_adjustment (itu ditentukan oleh manajer)
- Catat anomali atau hal yang perlu diperhatikan di ai_calculation_notes

[Data Staf]
- Nama: {$data['staff_name']}
- Role: {$data['role_type']}
- Bulan: {$data['calc_month']}
- Gaji pokok: Rp {$data['base_salary']}
- Hari hadir: {$data['attendance_days']} hari
- Hari absen (disetujui): {$data['absent_days']} hari
- Tingkat penyelesaian tugas: {$rate}%
- Hasil penilaian kinerja: {$band}

[Format JSON Output]
{
  "deductions": angka_IDR,
  "overtime_pay": angka_IDR,
  "allowances": angka_IDR,
  "ai_calculation_notes": "Catatan singkat untuk manajer (anomali, hal perlu diperhatikan, maks 3 kalimat)"
}
PROMPT;
    }
}