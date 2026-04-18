<?php

namespace App\Http\Controllers;

use App\Models\InternalChatLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class StaffChatController extends Controller
{
    private string $apiUrl = 'https://api.anthropic.com/v1/messages';
    private string $model  = 'claude-sonnet-4-20250514';

    // ================================================================
    // チャット送信
    // ================================================================
    public function send(Request $request)
    {
        $request->validate([
            'message'    => 'required|string|max:2000',
            'session_id' => 'required|string',
            'history'    => 'nullable|array',
        ]);

        $user      = Auth::user();
        $message   = $request->message;
        $sessionId = $request->session_id;
        $history   = $request->history ?? [];

        // ロール別システムプロンプト
        $systemPrompt = $this->buildSystemPrompt($user->role_type);

        // Claude API 呼び出し
        $result = $this->callClaude($systemPrompt, $message, $history);

        if (!$result['success']) {
            return response()->json(['error' => 'AI tidak dapat merespons saat ini.'], 500);
        }

        // internal_chat_logs（ユーザー発言）
        InternalChatLog::create([
            'user_id'             => $user->id,
            'role_type'           => $user->role_type,
            'session_id'          => $sessionId,
            'message_role'        => 'user',
            'message_content'     => $message,
            'message_content_ja'  => $message,
            'source_language'     => 'id',
            'is_task_instruction' => false, // スタッフは指示出し不可
            'model_name'          => $this->model,
        ]);

        // internal_chat_logs（AI返答）
        InternalChatLog::create([
            'user_id'             => $user->id,
            'role_type'           => $user->role_type,
            'session_id'          => $sessionId,
            'message_role'        => 'assistant',
            'message_content'     => $result['text'],
            'message_content_ja'  => $result['text'],
            'source_language'     => 'id',
            'is_task_instruction' => false,
            'tokens_used'         => $result['tokens'],
            'model_name'          => $this->model,
        ]);

        return response()->json([
            'message' => $result['text'],
            'tokens'  => $result['tokens'],
        ]);
    }

    // ================================================================
    // チャット履歴取得
    // ================================================================
    public function history(Request $request)
    {
        $user = Auth::user();

        $logs = InternalChatLog::where('user_id', $user->id)
            ->orderBy('created_at', 'asc')
            ->limit(100)
            ->get([
                'id', 'session_id', 'message_role',
                'message_content', 'message_content_ja',
                'is_task_instruction', 'created_at',
            ]);

        return response()->json(['history' => $logs]);
    }

    // ================================================================
    // Private: ロール別システムプロンプト
    // ================================================================
    private function buildSystemPrompt(string $roleType): string
    {
        return match($roleType) {
            'investigator_user' => $this->investigatorPrompt(),
            'admin_user'        => $this->adminPrompt(),
            default             => $this->generalStaffPrompt(),
        };
    }

    private function investigatorPrompt(): string
    {
        return <<<PROMPT
Anda adalah asisten AI untuk staf Tim Investigasi HRI System.
Bantu investigator dalam pertanyaan seputar:
- Prosedur verifikasi data pemohon
- Panduan kategori investigasi (identitas, pendidikan, pekerjaan, sertifikasi, perilaku kerja)
- Cara mengisi laporan investigasi
- Pertanyaan umum seputar tugas investigasi

ATURAN WAJIB:
- Anda TIDAK berwenang membuat instruksi tugas untuk orang lain
- Anda TIDAK berwenang mengakses data pemohon secara langsung
- Jangan menyebut nama lengkap, NIK, atau data pribadi pemohon
- Jawab dalam Bahasa Indonesia
- Berikan panduan praktis dan konkret
PROMPT;
    }

    private function adminPrompt(): string
    {
        return <<<PROMPT
Anda adalah asisten AI untuk staf Tim Manajemen Peninjauan HRI System.
Bantu admin dalam pertanyaan seputar:
- Prosedur peninjauan dan keputusan sertifikasi
- Interpretasi skor dan panduan kebijakan
- Cara mengisi formulir keputusan
- Pertanyaan umum seputar tugas peninjauan

ATURAN WAJIB:
- Anda TIDAK berwenang membuat instruksi tugas untuk orang lain
- Anda TIDAK berwenang mengakses data pemohon secara langsung
- Jangan menyebut nama lengkap, NIK, atau data pribadi pemohon
- Jawab dalam Bahasa Indonesia
- Berikan panduan yang adil dan berbasis data
PROMPT;
    }

    private function generalStaffPrompt(): string
    {
        return <<<PROMPT
Anda adalah asisten AI untuk staf internal HRI System.
Bantu staf dalam:
- Pertanyaan seputar prosedur kerja dan kebijakan internal
- Bantuan pengisian laporan tugas
- Informasi tentang pengajuan izin dan cuti
- Pertanyaan umum seputar operasional HRI

ATURAN WAJIB:
- Anda TIDAK berwenang membuat instruksi tugas untuk orang lain
- Anda TIDAK berwenang mengakses data pribadi anggota
- Jawab dalam Bahasa Indonesia
- Berikan jawaban yang praktis dan mudah dipahami
PROMPT;
    }

    // ================================================================
    // Private: Claude API 呼び出し
    // ================================================================
    private function callClaude(string $systemPrompt, string $message, array $history): array
    {
        $apiKey = config('services.anthropic.api_key');

        $messages = [];
        foreach (array_slice($history, -6) as $h) {
            if (isset($h['role'], $h['content'])) {
                $messages[] = ['role' => $h['role'], 'content' => $h['content']];
            }
        }
        $messages[] = ['role' => 'user', 'content' => $message];

        try {
            $response = Http::withHeaders([
                'x-api-key'         => $apiKey,
                'anthropic-version' => '2023-06-01',
                'Content-Type'      => 'application/json',
            ])->timeout(60)->post($this->apiUrl, [
                'model'      => $this->model,
                'max_tokens' => 1000,
                'system'     => $systemPrompt,
                'messages'   => $messages,
            ]);

            if ($response->failed()) {
                return ['success' => false, 'text' => '', 'tokens' => 0];
            }

            $text   = $response->json()['content'][0]['text'] ?? '';
            $tokens = ($response->json()['usage']['input_tokens'] ?? 0)
                    + ($response->json()['usage']['output_tokens'] ?? 0);

            return ['success' => true, 'text' => $text, 'tokens' => $tokens];

        } catch (\Exception $e) {
            \Log::error('StaffChat error: ' . $e->getMessage());
            return ['success' => false, 'text' => '', 'tokens' => 0];
        }
    }
}