<?php

namespace App\Http\Controllers;

use App\Models\AiChatLog;
use App\Models\AiActivityLog;
use App\Models\PersonalDataAccessLog;
use App\Models\CertificationRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AiChatController extends Controller
{
    /**
     * 調査部チャットページ
     */
    public function investigatorIndex()
    {
        $requests = CertificationRequest::whereIn('current_status', [
                'under_investigation',
                'returned_internal',
            ])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($r) => [
                'id'      => $r->id,
                'case_no' => $r->case_no,
                'label'   => '案件 ' . $r->case_no,
            ]);

        return Inertia::render('Admin/Investigator/ChatPage', [
            'requests' => $requests,
        ]);
    }

    /**
     * 審査管理部チャットページ
     */
    public function adminIndex()
    {
        $requests = CertificationRequest::whereNotIn('current_status', ['draft'])
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn($r) => [
                'id'      => $r->id,
                'case_no' => $r->case_no,
                'label'   => '案件 ' . $r->case_no,
            ]);

        return Inertia::render('Admin/Admin/ChatPage', [
            'requests' => $requests,
        ]);
    }

    /**
     * AIチャット送信（共通エンドポイント）
     */
    public function send(Request $request)
    {
        $request->validate([
            'message'    => 'required|string|max:2000',
            'session_id' => 'required|string',
            'case_no'    => 'nullable|string',
            'history'    => 'nullable|array',
        ]);

        $user      = auth()->user();
        $caseNo    = $request->case_no;
        $sessionId = $request->session_id;
        $message   = $request->message;
        $history   = $request->history ?? [];

        // PII チェック
        $containsPii = $this->detectPii($message);
        $maskedMessage = $containsPii ? $this->maskPii($message) : $message;

        // PII を含む場合はブロック
        if ($containsPii) {
            $this->saveChatLog(
                $user, $sessionId, $caseNo,
                'user', $maskedMessage,
                true, null, 'PII_DETECTED'
            );
            return response()->json([
                'error' => 'Pesan mengandung informasi pribadi yang tidak diizinkan. Gunakan "Pemohon A" atau ID kasus sebagai pengenal.',
            ], 422);
        }

        // システムプロンプト構築
        $systemPrompt = $this->buildSystemPrompt($user->role_type, $caseNo);

        // メッセージ履歴を構築
        $messages = [];
        foreach ($history as $h) {
            if (isset($h['role'], $h['content'])) {
                $messages[] = [
                    'role'    => $h['role'],
                    'content' => $h['content'],
                ];
            }
        }
        $messages[] = ['role' => 'user', 'content' => $maskedMessage];

        // Anthropic API 呼び出し
        $startTime  = microtime(true);
        $aiResponse = $this->callAnthropic($systemPrompt, $messages);
        $elapsed    = round((microtime(true) - $startTime) * 1000);

        if (!$aiResponse['success']) {
            return response()->json([
                'error' => 'AI tidak dapat merespons saat ini. Silakan coba lagi.',
            ], 500);
        }

        $assistantText = $aiResponse['text'];
        $tokensUsed    = $aiResponse['tokens'] ?? 0;
        $modelName     = 'claude-sonnet-4-6';

        // チャットログ保存
        $this->saveChatLog($user, $sessionId, $caseNo, 'user', $maskedMessage, false, $tokensUsed, null, $modelName);
        $this->saveChatLog($user, $sessionId, $caseNo, 'assistant', $assistantText, false, $tokensUsed, null, $modelName);

        // AI活動ログ
        AiActivityLog::create([
            'case_no'           => $caseNo,
            'log_type'          => 'chat',
            'service_name'      => 'AiChatService',
            'action_type'       => 'chat',
            'model_name'        => $modelName,
            'tokens_used'       => $tokensUsed,
            'response_time_ms'  => $elapsed,
            'triggered_by'      => $user->id,
            'is_anonymized'     => true,
        ]);

        // 個人データアクセスログ
        if ($caseNo) {
            PersonalDataAccessLog::create([
                'case_no'          => $caseNo,
                'accessor_user_id' => $user->id,
                'accessor_role'    => $user->role_type,
                'data_type'        => 'ai_chat_context',
                'action'           => 'ai_send',
                'access_reason'    => $user->role_type === 'investigator_user'
                    ? 'investigation'
                    : 'review',
                'ip_address'       => $request->ip(),
                'accessed_at'      => now(),
            ]);
        }

        return response()->json([
            'message' => $assistantText,
            'tokens'  => $tokensUsed,
        ]);
    }

    // ================================================================
// チャット履歴取得
// ================================================================
public function history(Request $request)
{
    $user = Auth::user();

    $logs = AiChatLog::where('user_id', $user->id)
        ->orderBy('created_at', 'asc')
        ->limit(200)
        ->get([
            'id', 'case_no', 'session_id',
            'message_role', 'message_content',
            'created_at',
        ]);

    return response()->json(['history' => $logs]);
}

    // ──────────────────────────────────────────────
    // Private: システムプロンプト構築
    // ──────────────────────────────────────────────

    private function buildSystemPrompt(string $roleType, ?string $caseNo): string
    {
        $base = $roleType === 'investigator_user'
            ? $this->investigatorBasePrompt()
            : $this->adminBasePrompt();

        if (!$caseNo) {
            return $base;
        }

        $certRequest = CertificationRequest::with([
            'investigationItems',
            'educationHistory',
            'workHistory',
            'certifications',
            'caseReviews',
            'reviewItems',
        ])->where('case_no', $caseNo)->first();

        if (!$certRequest) {
            return $base;
        }

        $context = $this->buildAnonymizedContext($certRequest, $roleType);
        return $base . "\n\n" . $context;
    }

    private function investigatorBasePrompt(): string
    {
        return <<<PROMPT
Anda adalah asisten AI untuk Tim Investigasi HRI System.
Tugas Anda: membantu investigator dalam memverifikasi data pemohon sesuai prosedur.

ATURAN WAJIB:
- Jangan pernah menyebut nama lengkap, NIK, nomor telepon, atau alamat pemohon
- Pemohon selalu disebut sebagai "Pemohon" atau menggunakan nomor kasus
- Fokus pada verifikasi faktual: pendidikan, pekerjaan, sertifikasi, perilaku kerja
- Ingatkan investigator jika ada potensi pelanggaran aturan investigasi

KATEGORI YANG DILARANG DIINVESTIGASI:
agama, kesehatan, orientasi seksual, status pernikahan, kehidupan pribadi, rumor, afiliasi politik

PANDUAN SKOR (sistem penambahan poin):
- identity  (maks 20): KTP, NIK, data dasar
- education (maks 15): ijazah, transkrip, konfirmasi lembaga
- work      (maks 25): surat keterangan, konfirmasi atasan
- certification (maks 10): sertifikat asli, nomor registrasi
- conduct   (maks 20): kehadiran, kepatuhan instruksi, kerja sama TIM (HANYA perilaku kerja)
- consistency (maks 10): kecocokan antar data

Jika ada item yang mencurigakan, sarankan untuk set policy_violation_flag = true.
Jawab dalam Bahasa Indonesia. Berikan panduan praktis dan konkret.
PROMPT;
    }

    private function adminBasePrompt(): string
    {
        return <<<PROMPT
Anda adalah asisten AI untuk Tim Manajemen Peninjauan HRI System.
Tugas Anda: membantu admin dalam pengambilan keputusan akhir sertifikasi.

ATURAN WAJIB:
- Jangan pernah menyebut nama lengkap, NIK, atau data pribadi pemohon
- Pemohon selalu disebut sebagai "Pemohon" atau menggunakan nomor kasus
- Anda TIDAK berwenang: memecat, memberi sanksi, atau membuat keputusan hukum
- Berikan analisis berimbang berdasarkan data yang tersedia

PANDUAN KEPUTUSAN (effective_decision):
- APPROVE              : base_score ≥ 75, truthfulness ≥ 80%, consistency ≥ 80%, tidak ada pelanggaran
- CONDITIONAL_APPROVE  : base_score ≥ 60, ada item belum terverifikasi, tidak ada pemalsuan besar
- REJECT               : identitas tidak terkonfirmasi, pemalsuan jelas, atau manipulasi dokumen
- ESCALATE_TO_HUMAN    : risiko hukum, risiko pencemaran nama baik, riwayat disiplin sulit diinterpretasi
- RETURN_TO_INVESTIGATION: ada pelanggaran aturan (policy_violation_flag=true) atau investigasi tidak lengkap

Jawab dalam Bahasa Indonesia. Bantu admin mengambil keputusan yang adil dan berbasis data.
PROMPT;
    }

    private function buildAnonymizedContext(CertificationRequest $req, string $roleType): string
    {
        $lines = [];
        $lines[] = "=== KONTEKS KASUS: {$req->case_no} ===";
        $lines[] = "Status        : {$req->current_status}";
        $lines[] = "Skor Dasar    : " . ($req->base_score ?? 'Belum dihitung');
        $lines[] = "Truthfulness  : " . ($req->truthfulness_percent ?? '-') . '%';
        $lines[] = "Consistency   : " . ($req->consistency_percent ?? '-') . '%';

        // 調査項目
        if ($req->investigationItems && $req->investigationItems->count()) {
            $lines[] = '';
            $lines[] = "--- Item Investigasi ---";
            foreach ($req->investigationItems as $item) {
                $note = $item->notes_id ? " — {$item->notes_id}" : '';
                $violation = $item->policy_violation_flag ? ' ⚠️VIOLATION' : '';
                $lines[] = "[{$item->category}] {$item->item_name}: {$item->validity}{$note}{$violation}";
            }
        }

        // 学歴（匿名化）
        if ($req->educationHistory && $req->educationHistory->count()) {
            $lines[] = '';
            $lines[] = "--- Riwayat Pendidikan ---";
            foreach ($req->educationHistory as $edu) {
                $lines[] = "{$edu->level} - {$edu->school} "
                    . "({$edu->graduation_date}): "
                    . ($edu->verification_status ?? '-');
            }
        }

        // 職歴（匿名化）
        if ($req->workHistory && $req->workHistory->count()) {
            $lines[] = '';
            $lines[] = "--- Riwayat Pekerjaan ---";
            foreach ($req->workHistory as $work) {
                $end = $work->end_date ?? 'Sekarang';
                $lines[] = "{$work->company} / {$work->position} "
                    . "({$work->start_date} ~ {$end}): "
                    . ($work->verification_status ?? '-');
            }
        }

        // 審査管理部のみ：AI判定結果を追加
        if ($roleType === 'admin_user' && $req->caseReviews && $req->caseReviews->count()) {
            $latest = $req->caseReviews->sortBy('id')->last();
            $lines[] = '';
            $lines[] = "--- Hasil Penilaian AI ---";
            $lines[] = "Keputusan AI   : " . ($latest->ai_proposed_decision ?? '-');
            $lines[] = "Alasan         : " . ($latest->claude_overall_reason ?? '-');
            $lines[] = "HRI Suitability: " . ($latest->hri_suitability_score ?? '-');
            $lines[] = "Work Ability   : " . ($latest->work_ability_band ?? '-');
            $lines[] = "Confidence     : " . ($latest->confidence_level ?? '-');
        }

        return implode("\n", $lines);
    }

    // ──────────────────────────────────────────────
    // Private: Anthropic API
    // ──────────────────────────────────────────────

    private function callAnthropic(string $systemPrompt, array $messages): array
    {
        $apiKey = config('services.anthropic.api_key');

        if (!$apiKey) {
            \Log::error('Anthropic API key not set.');
            return ['success' => false, 'text' => '', 'tokens' => 0];
        }

        $payload = [
            'model'      => 'claude-sonnet-4-6',
            'max_tokens' => 1024,
            'system'     => $systemPrompt,
            'messages'   => $messages,
        ];

        $ch = curl_init('https://api.anthropic.com/v1/messages');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'x-api-key: ' . $apiKey,
                'anthropic-version: 2023-06-01',
            ],
            CURLOPT_TIMEOUT        => 60,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) {
            \Log::error('Anthropic API error', [
                'code' => $httpCode,
                'body' => $response,
            ]);
            return ['success' => false, 'text' => '', 'tokens' => 0];
        }

        $data   = json_decode($response, true);
        $text   = $data['content'][0]['text'] ?? '';
        $tokens = ($data['usage']['input_tokens'] ?? 0)
                + ($data['usage']['output_tokens'] ?? 0);

        return ['success' => true, 'text' => $text, 'tokens' => $tokens];
    }

    // ──────────────────────────────────────────────
    // Private: PII 検出・マスク
    // ──────────────────────────────────────────────

    private function detectPii(string $text): bool
    {
        // NIK（16桁数字）またはインドネシア電話番号
        return (bool) preg_match('/\b\d{16}\b/', $text)
            || (bool) preg_match('/(\+62|08)\d{8,11}/', $text);
    }

    private function maskPii(string $text): string
    {
        $text = preg_replace('/\b\d{16}\b/', '[NIK-MASKED]', $text);
        $text = preg_replace('/(\+62|08)\d{8,11}/', '[PHONE-MASKED]', $text);
        return $text;
    }

    // ──────────────────────────────────────────────
    // Private: チャットログ保存
    // ──────────────────────────────────────────────

    private function saveChatLog(
        $user,
        string $sessionId,
        ?string $caseNo,
        string $role,
        string $content,
        bool $containsPii       = false,
        ?int $tokens            = null,
        ?string $blockedReason  = null,
        ?string $modelName      = null
    ): void {
        AiChatLog::create([
            'case_no'           => $caseNo,
            'user_id'           => $user->id,
            'role_type'         => $user->role_type,
            'session_id'        => $sessionId,
            'message_role'      => $role,
            'message_content'   => $content,
            'contains_pii_flag' => $containsPii,
            'blocked_reason'    => $blockedReason,
            'tokens_used'       => $tokens,
            'model_name'        => $modelName ?? 'claude-sonnet-4-6',
            'created_at'        => now(),
        ]);
    }
}