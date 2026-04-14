<?php

namespace App\Http\Controllers\President;

use App\Http\Controllers\Controller;
use App\Models\InternalChatLog;
use App\Models\ChatTaskBridgeLog;
use App\Models\AiTaskOrder;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PresidentChatController extends Controller
{
    private string $apiUrl = 'https://api.anthropic.com/v1/messages';
    private string $model  = 'claude-sonnet-4-20250514';

    // ================================================================
    // チャット送信（日本語入力 → 多言語翻訳 → 業務指示生成）
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

        // ① Claude に3言語翻訳 + 業務指示解析を依頼
        $analysisResult = $this->analyzeAndTranslate($message, $history);

        if (!$analysisResult['success']) {
            return response()->json(['error' => 'AI tidak dapat merespons saat ini.'], 500);
        }

        $responseJa = $analysisResult['response_ja'];    // 日本語返答
        $responseKo = $analysisResult['response_ko'];    // 韓国語翻訳
        $responseId = $analysisResult['response_id'];    // インドネシア語翻訳
        $isInstruction      = $analysisResult['is_task_instruction'];
        $interpretedInstruction = $analysisResult['interpreted_instruction'] ?? null;
        $targetDivision     = $analysisResult['target_division'] ?? null;
        $priorityLevel      = $analysisResult['priority_level'] ?? 'NORMAL';

        // ② internal_chat_logs に保存（ユーザー発言）
        $userLog = InternalChatLog::create([
            'user_id'              => $user->id,
            'role_type'            => $user->role_type,
            'session_id'           => $sessionId,
            'message_role'         => 'user',
            'message_content'      => $message,
            'message_content_ja'   => $message,
            'source_language'      => 'ja',
            'is_task_instruction'  => $isInstruction,
            'model_name'           => $this->model,
        ]);

        // ③ internal_chat_logs にAI返答を保存
        $assistantLog = InternalChatLog::create([
            'user_id'              => $user->id,
            'role_type'            => $user->role_type,
            'session_id'           => $sessionId,
            'message_role'         => 'assistant',
            'message_content'      => $responseJa,
            'message_content_ja'   => $responseJa,
            'message_content_ko'   => $responseKo,
            'message_content_id'   => $responseId,
            'source_language'      => 'ja',
            'is_task_instruction'  => $isInstruction,
            'tokens_used'          => $analysisResult['tokens'],
            'model_name'           => $this->model,
        ]);

        // ④ 業務指示の場合 → chat_task_bridge_logs + ai_task_orders を生成
        $taskOrderId  = null;
        $bridgeStatus = 'PENDING';

        if ($isInstruction && $interpretedInstruction) {
            // ai_task_orders に DRAFT で保存（管理者承認待ち）
            $taskOrder = AiTaskOrder::create([
                'order_no'             => 'ORD-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4)),
                'issued_by_user_id'    => $user->id,
                'instruction_title'    => mb_substr($message, 0, 50) . '...',
                'instruction_body'     => $interpretedInstruction,
                'target_division'      => $targetDivision ?? 'ADMIN',
                'priority_level'       => $priorityLevel,
                'approval_status'      => 'DRAFT',
                'ai_processing_status' => 'PENDING',
                'visibility_scope'     => 'INTERNAL',
                'due_at'               => $analysisResult['due_at'] ?? null,
            ]);

            $taskOrderId  = $taskOrder->id;
            $bridgeStatus = 'CONFIRMED';

            // chat_task_bridge_logs に記録
            ChatTaskBridgeLog::create([
                'internal_chat_log_id'   => $assistantLog->id,
                'issued_by_user_id'      => $user->id,
                'original_message_ja'    => $message,
                'interpreted_instruction'=> $interpretedInstruction,
                'target_division'        => $targetDivision,
                'priority_level'         => $priorityLevel,
                'task_order_created'     => true,
                'task_order_id'          => $taskOrderId,
                'bridge_status'          => $bridgeStatus,
            ]);

            // internal_chat_logs に task_order_id を紐づけ
            $assistantLog->update(['task_order_id' => $taskOrderId]);

            // audit_logs に記録
            AuditLog::recordHuman('TASK_ORDER_CREATED', null, [
                'new' => [
                    'order_no'       => $taskOrder->order_no,
                    'source'         => 'president_chat',
                    'target_division'=> $targetDivision,
                ],
            ]);
        }

        return response()->json([
            'message_ja'         => $responseJa,
            'message_ko'         => $responseKo,
            'message_id'         => $responseId,
            'is_task_instruction'=> $isInstruction,
            'task_order_id'      => $taskOrderId,
            'task_order_created' => $isInstruction && $taskOrderId !== null,
            'tokens'             => $analysisResult['tokens'],
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
    // Private: Claude API で翻訳 + 業務指示解析
    // ================================================================
    private function analyzeAndTranslate(string $message, array $history): array
    {
        $apiKey = config('services.anthropic.api_key');

        $systemPrompt = <<<PROMPT
あなたはHRIシステムのPresident専用AIアシスタントです。
Presidentが日本語で入力したメッセージを受け取り、以下のJSON形式のみで返答してください。
説明文・前置き・マークダウンは一切不要です。JSONのみ返してください。

【役割】
- Presidentの業務指示・質問に日本語で回答する
- 業務指示と判断した場合は3言語に翻訳する
- 業務指示の対象部署・優先度・期限を解析する

【業務指示の判断基準】
以下を含む場合は is_task_instruction = true:
- 「〜してください」「〜を調査して」「〜を確認して」「〜を作成して」等の命令形
- 特定の業務・タスクの実施を求める内容

【対象部署コード】
INVESTIGATION（調査部）/ ADMIN（審査管理部）/ STRATEGY（戦略M部）/ AI_DEV（AI開発部）/ MARKETING（マーケティング部）

【優先度】
URGENT / HIGH / NORMAL / LOW

【出力JSON形式】
{
  "response_ja": "日本語での返答（President向け）",
  "response_ko": "한국어 번역（local_manager향）",
  "response_id": "Terjemahan Bahasa Indonesia（staff向け）",
  "is_task_instruction": false,
  "interpreted_instruction": "業務指示の内容（is_task_instructionがtrueの場合のみ）",
  "target_division": "対象部署コード（is_task_instructionがtrueの場合のみ）",
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

            // JSON パース
            $cleaned = preg_replace('/^```json\s*/m', '', $rawText);
            $cleaned = preg_replace('/^```\s*/m', '', $cleaned);
            $decoded = json_decode(trim($cleaned), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return [
                    'success'              => true,
                    'response_ja'          => $rawText,
                    'response_ko'          => $rawText,
                    'response_id'          => $rawText,
                    'is_task_instruction'  => false,
                    'interpreted_instruction' => null,
                    'target_division'      => null,
                    'priority_level'       => 'NORMAL',
                    'due_at'               => null,
                    'tokens'               => $tokens,
                ];
            }

            return array_merge(['success' => true, 'tokens' => $tokens], $decoded);

        } catch (\Exception $e) {
            \Log::error('PresidentChat error: ' . $e->getMessage());
            return ['success' => false];
        }
    }
}