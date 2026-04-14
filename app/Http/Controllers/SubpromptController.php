<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Services\ClaudeSubpromptService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubpromptController extends Controller
{
    public function __construct(private ClaudeSubpromptService $service) {}

    // ──────────────────────────────────────────────────────────────
    // A-1: 認証審査
    // ──────────────────────────────────────────────────────────────
    public function runA1(Request $request): JsonResponse
    {
        $request->validate([
            'case_id'             => 'required',
            'investigation_records' => 'nullable|array',
        ]);

        $result = $this->service->runA1($request->all());

        $this->auditLog('SUBPROMPT_A1_EXECUTED', $request, [
            'case_id'       => $request->input('case_id'),
            'review_status' => $result['review_status'] ?? null,
        ]);

        return response()->json($result);
    }

    // ──────────────────────────────────────────────────────────────
    // A-2: 差し戻し判断
    // ──────────────────────────────────────────────────────────────
    public function runA2(Request $request): JsonResponse
    {
        $request->validate([
            'case_id' => 'required',
        ]);

        $result = $this->service->runA2($request->all());

        $this->auditLog('SUBPROMPT_A2_EXECUTED', $request, [
            'case_id'        => $request->input('case_id'),
            'return_required' => $result['return_required'] ?? null,
        ]);

        return response()->json($result);
    }

    // ──────────────────────────────────────────────────────────────
    // D-1: 一般会員問い合わせ分類
    // ──────────────────────────────────────────────────────────────
    public function runD1(Request $request): JsonResponse
    {
        $request->validate([
            'inquiry_id'      => 'required',
            'inquiry_content' => 'required|string',
        ]);

        $result = $this->service->runD1($request->all());

        $this->auditLog('SUBPROMPT_D1_EXECUTED', $request, [
            'inquiry_id' => $request->input('inquiry_id'),
            'category'   => $result['category'] ?? null,
            'priority'   => $result['priority'] ?? null,
        ]);

        return response()->json($result);
    }

    // ──────────────────────────────────────────────────────────────
    // D-3: 企業会員問い合わせ分類
    // ──────────────────────────────────────────────────────────────
    public function runD3(Request $request): JsonResponse
    {
        $request->validate([
            'inquiry_id'      => 'required',
            'inquiry_content' => 'required|string',
        ]);

        $result = $this->service->runD3($request->all());

        $this->auditLog('SUBPROMPT_D3_EXECUTED', $request, [
            'inquiry_id' => $request->input('inquiry_id'),
            'category'   => $result['category'] ?? null,
            'priority'   => $result['priority'] ?? null,
        ]);

        return response()->json($result);
    }

    // ──────────────────────────────────────────────────────────────
    // G-1: 見積書作成
    // ──────────────────────────────────────────────────────────────
    public function runG1(Request $request): JsonResponse
    {
        $request->validate([
            'client_name'  => 'required|string',
            'service_type' => 'required|string',
        ]);

        $result = $this->service->runG1($request->all());

        $this->auditLog('SUBPROMPT_G1_EXECUTED', $request, [
            'client_name'  => $request->input('client_name'),
            'service_type' => $request->input('service_type'),
            'risk_flags'   => $result['risk_flags'] ?? [],
        ]);

        return response()->json($result);
    }

    // ──────────────────────────────────────────────────────────────
    // I-3: VR シリアル発行判定
    // ──────────────────────────────────────────────────────────────
    public function runI3(Request $request): JsonResponse
    {
        $request->validate([
            'case_id'       => 'required',
            'review_status' => 'required|string',
        ]);

        $result = $this->service->runI3($request->all());

        $this->auditLog('SUBPROMPT_I3_EXECUTED', $request, [
            'case_id'       => $request->input('case_id'),
            'issuable'      => $result['issuable'] ?? false,
            'issuance_type' => $result['issuance_type'] ?? null,
        ]);

        return response()->json($result);
    }

    // ──────────────────────────────────────────────────────────────
    // K-2: 外部表示マスキング
    // ──────────────────────────────────────────────────────────────
    public function runK2(Request $request): JsonResponse
    {
        $request->validate([
            'data' => 'required|array',
        ]);

        $result = $this->service->runK2($request->input('data'));

        $this->auditLog('SUBPROMPT_K2_EXECUTED', $request, [
            'masked_fields_count' => $result['masked_fields_count'] ?? 0,
            'display_safe'        => $result['display_safe'] ?? false,
        ]);

        return response()->json($result);
    }

    // ──────────────────────────────────────────────────────────────
    // K-3: 人間承認待ち整形
    // ──────────────────────────────────────────────────────────────
    public function runK3(Request $request): JsonResponse
    {
        $request->validate([
            'draft_data'    => 'required|array',
            'approver_role' => 'nullable|string',
        ]);

        $result = $this->service->runK3(
            $request->input('draft_data'),
            $request->input('approver_role', 'admin_user')
        );

        $this->auditLog('SUBPROMPT_K3_EXECUTED', $request, [
            'approver_role'      => $request->input('approver_role'),
            'recommended_action' => $result['recommended_action'] ?? null,
        ]);

        return response()->json($result);
    }

    // ──────────────────────────────────────────────────────────────
    // 共通: audit_logs 記録
    // ──────────────────────────────────────────────────────────────
    private function auditLog(string $actionType, Request $request, array $extra = []): void
    {
        try {
            AuditLog::create([
                'action_type'   => $actionType,
                'actor_user_id' => Auth::id(),
                'description'   => $actionType . ' | ' . json_encode($extra, JSON_UNESCAPED_UNICODE),
                'ip_address'    => $request->ip(),
            ]);
        } catch (\Exception $e) {
            // ログ失敗は処理を止めない
        }
    }
}