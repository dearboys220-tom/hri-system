<?php

namespace App\Http\Controllers;

use App\Models\StaffEvaluation;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;

class StaffEvaluationController extends Controller
{
    public function index()
    {
        $evaluations = StaffEvaluation::with(['staff', 'approvedBy'])
            ->orderByDesc('created_at')
            ->paginate(20);

        $staffList = User::whereIn('role_type', [
                'investigator_user', 'admin_user', 'em_staff',
                'strategy_user', 'ai_dev_user', 'marketing_user',
            ])
            ->where('status', 'active')
            ->get(['id', 'name', 'role_type']);

        return Inertia::render('Manager/Evaluations/Index', [
            'evaluations' => $evaluations,
            'staffList'   => $staffList,
        ]);
    }

    public function generate(Request $request)
    {
        $request->validate([
            'staff_user_id'          => 'required|exists:users,id',
            'evaluation_period_from' => 'required|date',
            'evaluation_period_to'   => 'required|date|after_or_equal:evaluation_period_from',
            'evaluation_type'        => 'required|in:TASK_BASED,MONTHLY,QUARTERLY',
        ]);

        $staffUserId = $request->staff_user_id;
        $fromDate    = $request->evaluation_period_from;
        $toDate      = $request->evaluation_period_to;

        // Task stats
        $taskStats = DB::table('ai_task_assignments')
            ->where('assigned_user_id', $staffUserId)
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN task_status = "COMPLETED" THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN delay_flag = 1 THEN 1 ELSE 0 END) as delayed,
                SUM(CASE WHEN non_compliance_flag = 1 THEN 1 ELSE 0 END) as non_compliant
            ')
            ->first();

        $taskTotal        = $taskStats->total ?? 0;
        $taskCompleted    = $taskStats->completed ?? 0;
        $taskDelayed      = $taskStats->delayed ?? 0;
        $taskNonCompliant = $taskStats->non_compliant ?? 0;
        $completionRate   = $taskTotal > 0 ? round(($taskCompleted / $taskTotal) * 100, 1) : 0;

        // Report & evidence stats
        $reportStats = DB::table('ai_employee_reports')
            ->where('reported_by_user_id', $staffUserId)
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->selectRaw('
                COUNT(*) as total_reports,
                SUM(CASE WHEN evidence_file_path IS NOT NULL THEN 1 ELSE 0 END) as with_evidence,
                SUM(CASE WHEN inconsistency_flag = 1 THEN 1 ELSE 0 END) as inconsistent
            ')
            ->first();

        $totalReports    = $reportStats->total_reports ?? 0;
        $withEvidence    = $reportStats->with_evidence ?? 0;
        $inconsistentCnt = $reportStats->inconsistent ?? 0;
        $evidenceRate    = $totalReports > 0 ? round(($withEvidence / $totalReports) * 100, 1) : 0;

        // Attendance & absence
        $attendanceDays = DB::table('em_attendance')
            ->where('em_employee_id', $staffUserId)
            ->whereBetween('date', [$fromDate, $toDate])
            ->count();

        $absenceDays = DB::table('employee_absence_requests')
            ->where('staff_user_id', $staffUserId)
            ->where('approval_status', 'APPROVED')
            ->whereBetween('absence_from', [$fromDate, $toDate])
            ->count();

        $staffUser = User::find($staffUserId);

        // Claude API呼び出し
        $prompt = $this->buildEvaluationPrompt([
            'staff_name'      => $staffUser->name,
            'role_type'       => $staffUser->role_type,
            'period_from'     => $fromDate,
            'period_to'       => $toDate,
            'evaluation_type' => $request->evaluation_type,
            'task_total'      => $taskTotal,
            'task_completed'  => $taskCompleted,
            'completion_rate' => $completionRate,
            'delayed'         => $taskDelayed,
            'non_compliant'   => $taskNonCompliant,
            'total_reports'   => $totalReports,
            'evidence_rate'   => $evidenceRate,
            'inconsistent'    => $inconsistentCnt,
            'attendance_days' => $attendanceDays,
            'absence_days'    => $absenceDays,
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

        $band        = $result['ai_performance_band'] ?? 'FAIR';
        $score       = $result['ai_score'] ?? 50;
        $summary     = $result['ai_evaluation_summary'] ?? 'Ringkasan penilaian tidak tersedia.';
        $detail      = $result['ai_evaluation_detail'] ?? [];
        $recommended = $result['ai_recommended_action'] ?? 'MONITOR';

        $evaluation = StaffEvaluation::create([
            'staff_user_id'           => $staffUserId,
            'evaluation_period_from'  => $fromDate,
            'evaluation_period_to'    => $toDate,
            'evaluation_type'         => $request->evaluation_type,
            'ai_performance_band'     => $band,
            'ai_score'                => $score,
            'ai_evaluation_summary'   => $summary,
            'ai_evaluation_detail'    => $detail,
            'ai_recommended_action'   => $recommended,
            'warning_draft_triggered' => false,
        ]);

        AuditLog::recordHuman('STAFF_EVALUATED', null, [
            'new' => [
                'evaluation_id'       => $evaluation->id,
                'staff_user_id'       => $staffUserId,
                'ai_performance_band' => $band,
                'ai_score'            => $score,
                'staff_name'          => $staffUser->name,
            ],
        ]);

        return back()->with('success', 'Draft penilaian untuk ' . $staffUser->name . ' berhasil dibuat (' . $band . ')');
    }

    public function approve(Request $request, StaffEvaluation $evaluation)
    {
        $request->validate([
            'human_final_band'      => 'required|in:GOOD,FAIR,WARNING',
            'human_override_reason' => 'nullable|string|max:500',
        ]);

        $evaluation->update([
            'human_final_band'      => $request->human_final_band,
            'human_override_reason' => $request->human_override_reason,
            'approved_by_user_id'   => Auth::id(),
            'approved_at'           => now(),
        ]);

        AuditLog::recordHuman('EVALUATION_APPROVED', null, [
            'new' => [
                'evaluation_id'         => $evaluation->id,
                'human_final_band'      => $request->human_final_band,
                'human_override_reason' => $request->human_override_reason,
                'staff_name'            => $evaluation->staff->name,
            ],
        ]);

        return back()->with('success', 'Penilaian berhasil dikonfirmasi (' . $request->human_final_band . ')');
    }

    private function buildEvaluationPrompt(array $data): string
    {
        return <<<PROMPT
Anda adalah AI asisten evaluasi kinerja untuk sistem HRI.
Baca data staf berikut dan kembalikan hasil penilaian dalam format JSON saja.
Jangan sertakan teks penjelasan, pendahuluan, atau tanda backtick. Kembalikan JSON saja.

[Aturan Penilaian]
- GOOD: Tingkat penyelesaian tugas >=80%, tidak ada keterlambatan, tingkat bukti >=90%
- FAIR: Penyelesaian 60-79%, keterlambatan ringan, bukti 70-89%
- WARNING: Penyelesaian <60%, keterlambatan berulang, bukti tidak ada, banyak inkonsistensi

[Ekspresi yang Dilarang]
- Jangan gunakan pernyataan definitif seperti "pemecatan direkomendasikan", "pelanggaran hukum pasti"
- Jangan gunakan ekspresi yang menyerang karakter pribadi
- Gunakan ekspresi berbasis angka seperti "X tugas belum selesai", "tingkat penyelesaian X%"

[Data Staf]
- Nama: {$data['staff_name']}
- Role: {$data['role_type']}
- Periode: {$data['period_from']} s/d {$data['period_to']}
- Jenis Evaluasi: {$data['evaluation_type']}
- Total tugas: {$data['task_total']}
- Tugas selesai: {$data['task_completed']} (tingkat: {$data['completion_rate']}%)
- Keterlambatan: {$data['delayed']}
- Pelanggaran: {$data['non_compliant']}
- Total laporan: {$data['total_reports']}
- Tingkat bukti: {$data['evidence_rate']}%
- Laporan inkonsisten: {$data['inconsistent']}
- Hari hadir: {$data['attendance_days']}
- Hari absen (disetujui): {$data['absence_days']}

[Format JSON Output]
{
  "ai_performance_band": "GOOD | FAIR | WARNING",
  "ai_score": angka 0-100,
  "ai_evaluation_summary": "Ringkasan untuk manajer (2-4 kalimat, berbasis angka, tanpa pernyataan definitif)",
  "ai_evaluation_detail": {
    "completion_rate": angka,
    "evidence_rate": angka,
    "delayed_count": angka,
    "non_compliant_count": angka,
    "inconsistent_count": angka,
    "attendance_days": angka,
    "absence_days": angka
  },
  "ai_recommended_action": "COMMEND | MONITOR | WARNING_DRAFT"
}
PROMPT;
    }
}