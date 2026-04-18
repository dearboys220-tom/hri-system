<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\InternalChatLog;
use App\Models\ChatTaskBridgeLog;
use App\Models\AiTaskOrder;
use App\Models\AiTaskAssignment;
use App\Models\StaffAvailability;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ManagerChatController extends Controller
{
    private string $apiUrl = 'https://api.anthropic.com/v1/messages';
    private string $model  = 'claude-sonnet-4-20250514';

    // ================================================================
    // チャット送信
    // ================================================================
    public function send(Request $request)
    {
        $request->validate([
            'message'        => 'required|string|max:2000',
            'session_id'     => 'required|string',
            'history'        => 'nullable|array',
            'assignee_ids'   => 'nullable|array',
            'assignee_ids.*' => 'integer|exists:users,id',
        ]);

        $user        = Auth::user();
        $message     = $request->message;
        $sessionId   = $request->session_id;
        $history     = $request->history ?? [];
        $assigneeIds = $request->assignee_ids ?? [];

        // Claude API で意図解析・指示生成
        $analysisResult = $this->analyzeInstruction($message, $history);

        if (!$analysisResult['success']) {
            return response()->json(['error' => 'AI tidak dapat merespons saat ini.'], 500);
        }

        $responseText        = $analysisResult['response'];
        $isInstruction       = $analysisResult['is_task_instruction'];
        $interpretedInstruction = $analysisResult['interpreted_instruction'] ?? null;
        $targetDivision      = $analysisResult['target_division'] ?? 'ADMIN';
        $priorityLevel       = $analysisResult['priority_level'] ?? 'NORMAL';

        // internal_chat_logs（ユーザー発言）
        $userLog = InternalChatLog::create([
            'user_id'             => $user->id,
            'role_type'           => $user->role_type,
            'session_id'          => $sessionId,
            'message_role'        => 'user',
            'message_content'     => $message,
            'message_content_ja'  => $message,
            'source_language'     => 'id',
            'is_task_instruction' => $isInstruction,
            'model_name'          => $this->model,
        ]);

        // internal_chat_logs（AI返答）
        $assistantLog = InternalChatLog::create([
            'user_id'             => $user->id,
            'role_type'           => $user->role_type,
            'session_id'          => $sessionId,
            'message_role'        => 'assistant',
            'message_content'     => $responseText,
            'message_content_ja'  => $responseText,
            'source_language'     => 'id',
            'is_task_instruction' => $isInstruction,
            'tokens_used'         => $analysisResult['tokens'],
            'model_name'          => $this->model,
        ]);

        // 業務指示の場合 → ai_task_orders を自動生成
        $taskOrderId  = null;
        $taskAssigned = false;

        if ($isInstruction && $interpretedInstruction) {

            DB::transaction(function () use (
                $user, $message, $interpretedInstruction,
                $targetDivision, $priorityLevel, $assigneeIds,
                $analysisResult, $assistantLog, &$taskOrderId, &$taskAssigned
            ) {
                $taskOrder = AiTaskOrder::create([
                    'order_no'             => 'ORD-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4)),
                    'issued_by_user_id'    => $user->id,
                    'approver_user_id'     => $user->id,
                    'instruction_title'    => mb_substr($message, 0, 50),
                    'instruction_body'     => $interpretedInstruction,
                    'target_division'      => $targetDivision,
                    'priority_level'       => $priorityLevel,
                    'approval_status'      => AiTaskOrder::APPROVAL_APPROVED,
                    'ai_processing_status' => AiTaskOrder::AI_ASSIGNED,
                    'visibility_scope'     => 'INTERNAL',
                    'due_at'               => $analysisResult['due_at'] ?? null,
                ]);

                $taskOrderId = $taskOrder->id;

                if (!empty($assigneeIds)) {
                    foreach ($assigneeIds as $userId) {
                        $assignmentData = [
                            'task_order_id'     => $taskOrder->id,
                            'employee_user_id'  => $userId,
                            'task_status'       => AiTaskAssignment::STATUS_ASSIGNED,
                            'assigned_by_ai_at' => now(),
                            'due_at'            => $taskOrder->due_at,
                        ];

                        if (in_array('order_no', (new AiTaskAssignment())->getFillable())) {
                            $assignmentData['order_no'] = $taskOrder->order_no;
                        }

                        AiTaskAssignment::create($assignmentData);

                        $avail = StaffAvailability::where('staff_user_id', $userId)->first();
                        if ($avail) {
                            $avail->incrementTaskCount();
                        }
                    }
                    $taskAssigned = true;
                }

                ChatTaskBridgeLog::create([
                    'internal_chat_log_id'    => $assistantLog->id,
                    'issued_by_user_id'       => $user->id,
                    'original_message_ja'     => $message,
                    'interpreted_instruction' => $interpretedInstruction,
                    'target_division'         => $targetDivision,
                    'priority_level'          => $priorityLevel,
                    'task_order_created'      => true,
                    'task_order_id'           => $taskOrderId,
                    'bridge_status'           => 'CONFIRMED',
                ]);

                $assistantLog->update(['task_order_id' => $taskOrderId]);

                AuditLog::recordHuman('TASK_ORDER_CREATED', null, [
                    'new' => [
                        'order_no'             => $taskOrder->order_no,
                        'source'               => 'manager_chat',
                        'target_division'      => $targetDivision,
                        'assignee_count'       => count($assigneeIds),
                        'immediately_approved' => true,
                    ],
                ]);
            });
        }

        return response()->json([
            'message'             => $responseText,
            'is_task_instruction' => $isInstruction,
            'task_order_id'       => $taskOrderId,
            'task_order_created'  => $isInstruction && $taskOrderId !== null,
            'task_assigned'       => $taskAssigned,
            'assignee_count'      => count($assigneeIds),
            'tokens'              => $analysisResult['tokens'],
        ]);
    }

    // ================================================================
    // チャット履歴取得
    // ================================================================
    public function history(Request $request)
    {
        $user = Auth::user();

        $logs = InternalChatLog::where('user_id', $user->id)
            ->orderBy('created_at')
            ->limit(100)
            ->get([
                'id', 'session_id', 'message_role',
                'message_content', 'message_content_ja',
                'is_task_instruction', 'task_order_id',
                'created_at',
            ]);

        return response()->json(['history' => $logs]);
    }

    // ================================================================
    // Private: Claude API 呼び出し
    // ================================================================
    private function analyzeInstruction(string $message, array $history): array
    {
        $apiKey = config('services.anthropic.api_key');

        $systemPrompt = <<<PROMPT
Anda adalah asisten AI khusus Local Manager di sistem HRI.
Terima pesan dari Manager dan kembalikan HANYA dalam format JSON berikut.
Tidak perlu penjelasan atau teks pendahuluan.

Kriteria instruksi tugas:
Jika pesan mengandung perintah seperti "tolong", "mohon", "harap", "lakukan", "kerjakan", "buat", "kirim",
atau kata kerja imperatif lainnya, maka is_task_instruction = true.

Kode divisi target:
INVESTIGATION = Tim Investigasi
ADMIN = Tim Manajemen Peninjauan
STRATEGY = Departemen Strategi
AI_DEV = Departemen AI Dev
MARKETING = Departemen Marketing

Prioritas: URGENT / HIGH / NORMAL / LOW

Format output JSON:
{
  "response": "Respons dalam Bahasa Indonesia untuk Manager",
  "is_task_instruction": false,
  "interpreted_instruction": "Isi instruksi jika is_task_instruction = true, null jika tidak",
  "target_division": "Kode divisi target",
  "priority_level": "NORMAL",
  "due_at": null
}
PROMPT;

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
                return ['success' => false];
            }

            $rawText = $response->json()['content'][0]['text'] ?? '';
            $tokens  = ($response->json()['usage']['input_tokens'] ?? 0)
                     + ($response->json()['usage']['output_tokens'] ?? 0);

            $cleaned = preg_replace('/^```json\s*/m', '', $rawText);
            $cleaned = preg_replace('/^```\s*/m', '', $cleaned);
            $decoded = json_decode(trim($cleaned), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return [
                    'success'                 => true,
                    'response'                => $rawText,
                    'is_task_instruction'     => false,
                    'interpreted_instruction' => null,
                    'target_division'         => null,
                    'priority_level'          => 'NORMAL',
                    'due_at'                  => null,
                    'tokens'                  => $tokens,
                ];
            }

            return array_merge(['success' => true, 'tokens' => $tokens], $decoded);

        } catch (\Exception $e) {
            \Log::error('ManagerChat error: ' . $e->getMessage());
            return ['success' => false];
        }
    }
}