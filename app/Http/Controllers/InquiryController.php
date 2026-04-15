<?php

namespace App\Http\Controllers;

use App\Models\Inquiry;
use App\Models\AuditLog;
use App\Services\ClaudeSubpromptService;
use App\Services\NumberingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class InquiryController extends Controller
{
    public function __construct(
        private ClaudeSubpromptService $claude,
        private NumberingService $numbering
    ) {}

    // ─────────────────────────────────────────────
    // 【一般会員・企業会員】問い合わせ送信フォーム
    // ─────────────────────────────────────────────
    public function create()
    {
        return Inertia::render('Inquiry/Create');
    }

    // ─────────────────────────────────────────────
    // 【一般会員・企業会員】問い合わせ送信
    // ─────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'body'    => 'required|string|max:5000',
        ]);

        $user     = Auth::user();
        $userType = $user->role_type === 'company' ? 'company' : 'applicant';

        DB::beginTransaction();
        try {
            // ── 問い合わせ番号採番（Section 28.1: HRI-QRY-YYYYMMDD-NNNN）
            $today      = Carbon::now('Asia/Jakarta')->format('Ymd');
            $inquiryNo  = $this->numbering->generate('QRY', $today);

            // ── SLA設定（Section 30.3）
            $slaDeadline = Inquiry::calcSlaDeadline($userType);

            // ── レコード作成
            $inquiry = Inquiry::create([
                'inquiry_no'   => $inquiryNo,
                'user_id'      => $user->id,
                'user_type'    => $userType,
                'subject'      => $request->subject,
                'body'         => $request->body,
                'status'       => Inquiry::STATUS_RECEIVED,
                'sla_deadline' => $slaDeadline,
            ]);

            // ── AI分類（D-1/D-3）を非同期ジョブ代わりに同期実行
            $this->classifyWithAi($inquiry);

            // ── 採番ログ（Section 28.4）
            AuditLog::create([
                'user_id'     => $user->id,
                'action_type' => 'NUMBER_ISSUED',
                'target_type' => 'inquiries',
                'target_id'   => $inquiry->id,
                'new_value'   => json_encode(['inquiry_no' => $inquiryNo]),
                'notes'       => 'Nomor pertanyaan diterbitkan',
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Pertanyaan Anda telah berhasil dikirim. Kami akan merespons sesuai SLA yang berlaku.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan. Silakan coba lagi.']);
        }
    }

    // ─────────────────────────────────────────────
    // 【一般会員】自分の問い合わせ一覧
    // ─────────────────────────────────────────────
    public function myList()
    {
        $inquiries = Inquiry::where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->paginate(10);

        return Inertia::render('Inquiry/MyList', compact('inquiries'));
    }

    // ─────────────────────────────────────────────
    // 【管理者】問い合わせ管理一覧
    // ─────────────────────────────────────────────
    public function adminIndex(Request $request)
    {
        $query = Inquiry::with(['user', 'repliedBy'])
            ->orderByRaw("FIELD(status, 'received', 'classified', 'escalated', 'answered', 'closed')")
            ->orderByDesc('created_at');

        // フィルター
        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->user_type) {
            $query->where('user_type', $request->user_type);
        }
        if ($request->priority) {
            $query->where('ai_priority', $request->priority);
        }

        $inquiries = $query->paginate(20);

        // SLA超過チェック・更新
        Inquiry::where('status', '!=', 'closed')
            ->where('status', '!=', 'answered')
            ->where('sla_deadline', '<', now())
            ->where('sla_breached', false)
            ->update(['sla_breached' => true]);

        $stats = [
            'received'   => Inquiry::where('status', 'received')->count(),
            'classified' => Inquiry::where('status', 'classified')->count(),
            'escalated'  => Inquiry::where('status', 'escalated')->count(),
            'sla_breach' => Inquiry::where('sla_breached', true)
                                   ->whereNotIn('status', ['answered', 'closed'])->count(),
        ];

        return Inertia::render('Inquiry/Admin/Index', compact('inquiries', 'stats'));
    }

    // ─────────────────────────────────────────────
    // 【管理者】問い合わせ詳細
    // ─────────────────────────────────────────────
    public function adminShow(Inquiry $inquiry)
    {
        $inquiry->load(['user', 'repliedBy']);
        return Inertia::render('Inquiry/Admin/Show', compact('inquiry'));
    }

    // ─────────────────────────────────────────────
    // 【管理者】回答送信
    // ─────────────────────────────────────────────
    public function reply(Request $request, Inquiry $inquiry)
    {
        $request->validate([
            'human_reply' => 'required|string|max:10000',
        ]);

        $inquiry->update([
            'status'             => Inquiry::STATUS_ANSWERED,
            'human_reply'        => $request->human_reply,
            'replied_by_user_id' => Auth::id(),
            'replied_at'         => now(),
        ]);

        AuditLog::create([
            'user_id'     => Auth::id(),
            'action_type' => 'INQUIRY_REPLIED',
            'target_type' => 'inquiries',
            'target_id'   => $inquiry->id,
            'notes'       => 'Pertanyaan dijawab oleh admin',
        ]);

        return redirect()->back()->with('success', 'Jawaban berhasil dikirim.');
    }

    // ─────────────────────────────────────────────
    // 【管理者】エスカレーション
    // ─────────────────────────────────────────────
    public function escalate(Inquiry $inquiry)
    {
        $inquiry->update(['status' => Inquiry::STATUS_ESCALATED]);

        AuditLog::create([
            'user_id'     => Auth::id(),
            'action_type' => 'INQUIRY_ESCALATED',
            'target_type' => 'inquiries',
            'target_id'   => $inquiry->id,
            'notes'       => 'Pertanyaan dieskalasi ke supervisor',
        ]);

        return redirect()->back()->with('success', 'Pertanyaan berhasil dieskalasi.');
    }

    // ─────────────────────────────────────────────
    // 【管理者】クローズ
    // ─────────────────────────────────────────────
    public function close(Inquiry $inquiry)
    {
        $inquiry->update([
            'status'    => Inquiry::STATUS_CLOSED,
            'closed_at' => now(),
        ]);

        AuditLog::create([
            'user_id'     => Auth::id(),
            'action_type' => 'INQUIRY_CLOSED',
            'target_type' => 'inquiries',
            'target_id'   => $inquiry->id,
            'notes'       => 'Pertanyaan ditutup',
        ]);

        return redirect()->back()->with('success', 'Pertanyaan berhasil ditutup.');
    }

    // ─────────────────────────────────────────────
    // AI分類（内部メソッド）D-1 / D-3
    // ─────────────────────────────────────────────
    private function classifyWithAi(Inquiry $inquiry): void
    {
        try {
            $input = [
                'inquiry_id' => (string) $inquiry->id,
                'subject'    => $inquiry->subject,
                'body'       => $inquiry->body,
                'user_type'  => $inquiry->user_type,
            ];

            // ユーザータイプに応じてD-1/D-3を切り替え
            $result = $inquiry->user_type === 'company'
                ? $this->claude->classifyCompanyInquiry($input)   // D-3
                : $this->claude->classifyGeneralInquiry($input);   // D-1

            if (!isset($result['error'])) {
                $inquiry->update([
                    'status'                        => Inquiry::STATUS_CLASSIFIED,
                    'ai_category'                   => $result['category'] ?? null,
                    'ai_priority'                   => $result['priority'] ?? 'normal',
                    'ai_can_answer_immediately'     => $result['can_answer_immediately'] ?? false,
                    'ai_answer_prohibited'          => $result['answer_prohibited'] ?? false,
                    'ai_identity_check_required'    => $result['identity_check_required'] ?? false,
                    'ai_requires_supervisor_review' => $result['requires_supervisor_review'] ?? false,
                    'ai_requires_legal_review'      => $result['requires_legal_review'] ?? false,
                    'ai_requires_pdp_review'        => $result['requires_pdp_review'] ?? false,
                    'ai_should_escalate'            => $result['should_escalate_to_complaint_handling']
                                                        ?? $result['requires_special_escalation']
                                                        ?? false,
                    'ai_reason_summary'             => $result['reason_summary'] ?? null,
                    'ai_recommended_next_action'    => $result['recommended_next_action'] ?? null,
                    'ai_draft_reply_direction'      => $result['draft_reply_direction'] ?? null,
                    'ai_risk_flags'                 => $result['risk_flags'] ?? [],
                    'ai_classified_at'              => now(),
                    // SLAを苦情カテゴリで再計算
                    'sla_deadline' => Inquiry::calcSlaDeadline(
                        $inquiry->user_type,
                        $result['category'] ?? ''
                    ),
                ]);
            }
        } catch (\Exception $e) {
            // AI分類失敗時はreceivedのまま継続
            \Log::error('AI inquiry classification failed: ' . $e->getMessage());
        }
    }
}