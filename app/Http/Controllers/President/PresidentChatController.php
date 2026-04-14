<?php

namespace App\Http\Controllers\President;

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

class PresidentChatController extends Controller
{
    private string $apiUrl = 'https://api.anthropic.com/v1/messages';
    private string $model  = 'claude-sonnet-4-20250514';

    // ================================================================
    // チャット送信
    // 送信先スタッフを指定 → 即APPROVED・即割当（Manager承認不要）
    // ================================================================
    public function send(Request $request)
    {
        $request->validate([
            'message'      => 'required|string|max:2000',
            'session_id'   => 'required|string',
            'history'      => 'nullable|array',
            'assignee_ids' => 'nullable|array',
            'assignee_ids.*' => 'integer|exists:users,id',
        ]);

        $user        = Auth::user();
        $message     = $request->message;
        $sessionId   = $request->session_id;
        $history     = $request->history ?? [];
        $assigneeIds = $request->assignee_ids ?? [];

        // ① Claude に3言語翻訳 + 業務指示解析
        $analysisResult = $this->analyzeAndTranslate($message, $history);

        if (!$analysisResult['success']) {
            return response()->json(['error' => 'AI tidak dapat merespons saat ini.'], 500);
        }

        $responseJa          = $analysisResult['response_ja'];
        $responseKo          = $analysisResult['response_ko'];
        $responseId          = $analysisResult['response_id'];
        $isInstruction       = $analysisResult['is_task_instruction'];
        $interpretedInstruction = $analysisResult['interpreted_instruction'] ?? null;
        $targetDivision      = $analysisResult['target_division'] ?? 'ADMIN';
        $priorityLevel       = $analysisResult['priority_level'] ?? 'NORMAL';

        // ② internal_chat_logs（ユーザー発言）
        $userLog = InternalChatLog::create([
            'user_id'             => $user->id,
            'role_type'           => $user->role_type,
            'session_id'          => $sessionId,
            'message_role'        => 'user',
            'message_content'     => $message,
            'message_content_ja'  => $message,
            'source_language'     => 'ja',
            'is_task_instruction' => $isInstruction,
            'model_name'          => $this->model,
        ]);

        // ③ internal_chat_logs（AI返答）
        $assistantLog = InternalChatLog::create([
            'user_id'             => $user->id,
            'role_type'           => $user->role_type,
            'session_id'          => $sessionId,
            'message_role'        => 'assistant',
            'message_content'     => $responseJa,
            'message_content_ja'  => $responseJa,
            'message_content_ko'  => $responseKo,
            'message_content_id'  => $responseId,
            'source_language'     => 'ja',
            'is_task_instruction' => $isInstruction,
            'tokens_used'         => $analysisResult['tokens'],
            'model_name'          => $this->model,
        ]);

        // ④ 業務指示の場合 → 即APPROVED・即割当
        $taskOrderId    = null;
        $taskAssigned   = false;

        if ($isInstruction && $interpretedInstruction) {

            DB::transaction(function () use (
                $user, $message, $interpretedInstruction,
                $targetDivision, $priorityLevel, $assigneeIds,
                $analysisResult, $assistantLog, &$taskOrderId, &$taskAssigned
            ) {
                // ai_task_orders を即APPROVED で作成
                $taskOrder = AiTaskOrder::create([
                    'order_no'             => 'ORD-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4)),
                    'issued_by_user_id'    => $user->id,
                    'approver_user_id'     => $user->id, // Presidentが自己承認
                    'instruction_title'    => mb_substr($message, 0, 50),
                    'instruction_body'     => $interpretedInstruction,
                    'target_division'      => $targetDivision,
                    'priority_level'       => $priorityLevel,
                    'approval_status'      => AiTaskOrder::APPROVAL_APPROVED, // 即承認
                    'ai_processing_status' => AiTaskOrder::AI_ASSIGNED,
                    'visibility_scope'     => 'INTERNAL',
                    'due_at'               => $analysisResult['due_at'] ?? null,
                ]);

                $taskOrderId = $taskOrder->id;

                // 担当者が指定されている場合 → 即割当
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

                        // staff_availability 更新
                        $avail = StaffAvailability::where('staff_user_id', $userId)->first();
                        if ($avail) {
                            $avail->incrementTaskCount();
                        }
                    }
                    $taskAssigned = true;
                }

                // chat_task_bridge_logs
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
                        'order_no'        => $taskOrder->order_no,
                        'source'          => 'president_chat',
                        'target_division' => $targetDivision,
                        'assignee_count'  => count($assigneeIds),
                        'immediately_approved' => true,
                    ],
                ]);
            });
        }

        return response()->json([
            'message_ja'          => $responseJa,
            'message_ko'          => $responseKo,
            'message_id'          => $responseId,
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
        $user      = Auth::user();
        $sessionId = $request->query('session_id');

        $logs = InternalChatLog::where('user_id', $user->id)
            ->when($sessionId, fn($q) => $q->where('session_id', $sessionId))
            ->orderBy('created_at')
            ->limit(50)
            ->get(['id', 'message_role', 'message_content', 'message_content_ja',
                   'message_content_ko', 'message_content_id',
                   'is_task_instruction', 'task_order_id', 'created_at']);

        return response()->json(['history' => $logs]);
    }

    // ================================================================
    // Private: Claude API 翻訳 + 業務指示解析
    // ================================================================
    private function analyzeAndTranslate(string $message, array $history): array
    {
        $apiKey = config('services.anthropic.api_key');

        $systemPrompt = <<<PROMPT
Anda adalah asisten AI khusus President di sistem HRI.
Terima pesan input dari President dalam bahasa Jepang dan kembalikan HANYA dalam format JSON berikut.
Tidak perlu penjelasan atau teks pendahuluan.

【Kriteria instruksi tugas】
Jika mengandung perintah seperti "〜してください", "〜を調査して", "〜を確認して", "〜を作成して", maka is_task_instruction = true

【Kode divisi target】
INVESTIGATION（調査部）/ ADMIN（審査管理部）/ STRATEGY（戦略M部）/ AI_DEV（AI開発部）/ MARKETING（マーケティング部）

【Prioritas】
URGENT / HIGH / NORMAL / LOW

【Format output JSON】
{
  "response_ja": "返答（日本語・President向け）",
  "response_ko": "답변（한국어・local_manager향）",
  "response_id": "Respons dalam Bahasa Indonesia（staf향）",
  "is_task_instruction": false,
  "interpreted_instruction": "指示内容（is_task_instructionがtrueの場合）",
  "target_division": "対象部署コード",
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
                'max_tokens' => 1500,
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
                    'response_ja'             => $rawText,
                    'response_ko'             => $rawText,
                    'response_id'             => $rawText,
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
            \Log::error('PresidentChat error: ' . $e->getMessage());
            return ['success' => false];
        }
    }
}