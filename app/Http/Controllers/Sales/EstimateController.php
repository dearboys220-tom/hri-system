<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Estimate;
use App\Models\Order;
use App\Models\AuditLog;
use App\Services\ClaudeSubpromptService;
use App\Services\NumberingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class EstimateController extends Controller
{
    public function __construct(
        private ClaudeSubpromptService $claude,
        private NumberingService $numbering
    ) {}

    // ── 一覧
    public function index()
    {
        $estimates = Estimate::with(['createdBy', 'approvedBy'])
            ->orderByDesc('created_at')
            ->paginate(20);

        $stats = [
            'draft'            => Estimate::where('status', 'draft')->count(),
            'pending_approval' => Estimate::where('status', 'pending_approval')->count(),
            'sent'             => Estimate::where('status', 'sent')->count(),
            'accepted'         => Estimate::where('status', 'accepted')->count(),
        ];

        return Inertia::render('Sales/Estimates/Index', compact('estimates', 'stats'));
    }

    // ── 作成フォーム
    public function create()
    {
        return Inertia::render('Sales/Estimates/Create');
    }

    // ── AI見積生成（G-1）
    public function generateAi(Request $request)
    {
        $request->validate([
            'title'        => 'required|string|max:255',
            'client_name'  => 'required|string|max:255',
            'service_type' => 'required|string',
            'scope_notes'  => 'nullable|string',
            'budget_hint'  => 'nullable|string',
        ]);

        $input = [
            'title'        => $request->title,
            'client_name'  => $request->client_name,
            'service_type' => $request->service_type,
            'scope_notes'  => $request->scope_notes ?? '',
            'budget_hint'  => $request->budget_hint ?? '',
            'currency'     => 'IDR',
        ];

        $result = $this->claude->generateEstimate($input); // G-1

        if (isset($result['error'])) {
            return response()->json(['error' => 'AI生成に失敗しました'], 500);
        }

        return response()->json($result);
    }

    // ── 保存（draft）
    public function store(Request $request)
    {
        $request->validate([
            'title'           => 'required|string|max:255',
            'client_name'     => 'required|string|max:255',
            'client_email'    => 'nullable|email',
            'service_type'    => 'required|string',
            'scope_included'  => 'nullable|string',
            'scope_excluded'  => 'nullable|string',
            'subtotal'        => 'required|integer|min:0',
            'discount_exists' => 'boolean',
            'discount_amount' => 'nullable|integer|min:0',
            'discount_reason' => 'required_if:discount_exists,true|nullable|string',
            'final_amount'    => 'required|integer|min:0',
            'validity_days'   => 'required|integer|min:1',
            'payment_terms'   => 'nullable|string',
            'special_notes'   => 'nullable|string',
            'contract_required' => 'boolean',
            'nda_required'    => 'boolean',
            // AI生成結果
            'ai_estimate_body'      => 'nullable|string',
            'ai_cover_email_draft'  => 'nullable|string',
            'ai_approval_note'      => 'nullable|string',
            'ai_risk_flags'         => 'nullable|array',
            'ai_missing_items'      => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            $year       = Carbon::now('Asia/Jakarta')->year;
            $estimateNo = $this->numbering->generate('EST', 'SALES', $year);

            $validUntil = Carbon::now('Asia/Jakarta')->addDays($request->validity_days)->toDateString();

            $estimate = Estimate::create([
                'estimate_no'          => $estimateNo,
                'title'                => $request->title,
                'client_name'          => $request->client_name,
                'client_email'         => $request->client_email,
                'service_type'         => $request->service_type,
                'scope_included'       => $request->scope_included,
                'scope_excluded'       => $request->scope_excluded,
                'special_notes'        => $request->special_notes,
                'subtotal'             => $request->subtotal,
                'discount_exists'      => $request->discount_exists ?? false,
                'discount_amount'      => $request->discount_amount ?? 0,
                'discount_reason'      => $request->discount_reason,
                'final_amount'         => $request->final_amount,
                'tax_note'             => $request->tax_note,
                'payment_terms'        => $request->payment_terms,
                'validity_days'        => $request->validity_days,
                'valid_until'          => $validUntil,
                'contract_required'    => $request->contract_required ?? false,
                'nda_required'         => $request->nda_required ?? false,
                'status'               => Estimate::STATUS_DRAFT,
                'ai_estimate_body'     => $request->ai_estimate_body,
                'ai_cover_email_draft' => $request->ai_cover_email_draft,
                'ai_approval_note'     => $request->ai_approval_note,
                'ai_risk_flags'        => $request->ai_risk_flags ?? [],
                'ai_missing_items'     => $request->ai_missing_items ?? [],
                'ai_generated_at'      => $request->ai_estimate_body ? now() : null,
                'created_by_user_id'   => Auth::id(),
            ]);

            AuditLog::create([
                'user_id'     => Auth::id(),
                'action_type' => 'NUMBER_ISSUED',
                'target_type' => 'estimates',
                'target_id'   => $estimate->id,
                'new_value'   => json_encode(['estimate_no' => $estimateNo]),
                'notes'       => 'Nomor penawaran diterbitkan',
            ]);

            DB::commit();
            return redirect()->route('sales.estimates.show', $estimate->id)
                ->with('success', 'Penawaran berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Gagal menyimpan penawaran.']);
        }
    }

    // ── 詳細
    public function show(Estimate $estimate)
    {
        $estimate->load(['createdBy', 'approvedBy', 'order']);
        return Inertia::render('Sales/Estimates/Show', compact('estimate'));
    }

    // ── 承認申請（draft → pending_approval）
    public function submitForApproval(Estimate $estimate)
    {
        if ($estimate->status !== Estimate::STATUS_DRAFT) {
            return redirect()->back()->withErrors(['error' => 'Hanya draft yang bisa diajukan.']);
        }

        $estimate->update(['status' => Estimate::STATUS_PENDING_APPROVAL]);

        AuditLog::create([
            'user_id'     => Auth::id(),
            'action_type' => 'ESTIMATE_SUBMITTED',
            'target_type' => 'estimates',
            'target_id'   => $estimate->id,
            'notes'       => 'Penawaran diajukan untuk persetujuan',
        ]);

        return redirect()->back()->with('success', 'Penawaran diajukan untuk persetujuan.');
    }

    // ── 承認（pending_approval → approved）
    public function approve(Estimate $estimate)
    {
        if ($estimate->status !== Estimate::STATUS_PENDING_APPROVAL) {
            return redirect()->back()->withErrors(['error' => 'Status tidak valid untuk disetujui.']);
        }

        $estimate->update([
            'status'             => Estimate::STATUS_APPROVED,
            'approved_by_user_id' => Auth::id(),
            'approved_at'        => now(),
        ]);

        AuditLog::create([
            'user_id'     => Auth::id(),
            'action_type' => 'ESTIMATE_APPROVED',
            'target_type' => 'estimates',
            'target_id'   => $estimate->id,
            'notes'       => 'Penawaran disetujui',
        ]);

        return redirect()->back()->with('success', 'Penawaran disetujui.');
    }

    // ── 送付済み（approved → sent）
    public function markSent(Estimate $estimate)
    {
        if ($estimate->status !== Estimate::STATUS_APPROVED) {
            return redirect()->back()->withErrors(['error' => 'Hanya penawaran yang disetujui bisa dikirim.']);
        }

        $estimate->update([
            'status'  => Estimate::STATUS_SENT,
            'sent_at' => now(),
        ]);

        AuditLog::create([
            'user_id'     => Auth::id(),
            'action_type' => 'ESTIMATE_SENT',
            'target_type' => 'estimates',
            'target_id'   => $estimate->id,
            'notes'       => 'Penawaran dikirim ke klien',
        ]);

        return redirect()->back()->with('success', 'Penawaran ditandai sebagai terkirim.');
    }

    // ── 受注確定（sent → accepted → ordersレコード自動生成）
    public function accept(Estimate $estimate)
    {
        if ($estimate->status !== Estimate::STATUS_SENT) {
            return redirect()->back()->withErrors(['error' => 'Hanya penawaran yang terkirim bisa dikonfirmasi.']);
        }

        DB::beginTransaction();
        try {
            $estimate->update([
                'status'      => Estimate::STATUS_ACCEPTED,
                'accepted_at' => now(),
            ]);

            // 受注確認書の自動生成
            $year    = Carbon::now('Asia/Jakarta')->year;
            $orderNo = $this->numbering->generate('ORD', 'SALES', $year);

            $order = Order::create([
                'order_no'           => $orderNo,
                'estimate_id'        => $estimate->id,
                'client_name'        => $estimate->client_name,
                'client_email'       => $estimate->client_email,
                'service_type'       => $estimate->service_type,
                'final_amount'       => $estimate->final_amount,
                'payment_terms'      => $estimate->payment_terms,
                'status'             => Order::STATUS_CONFIRMED,
                'created_by_user_id' => Auth::id(),
            ]);

            AuditLog::create([
                'user_id'     => Auth::id(),
                'action_type' => 'NUMBER_ISSUED',
                'target_type' => 'orders',
                'target_id'   => $order->id,
                'new_value'   => json_encode(['order_no' => $orderNo]),
                'notes'       => 'Nomor pesanan diterbitkan',
            ]);

            AuditLog::create([
                'user_id'     => Auth::id(),
                'action_type' => 'ORDER_CREATED',
                'target_type' => 'orders',
                'target_id'   => $order->id,
                'notes'       => 'Pesanan dikonfirmasi dari penawaran ' . $estimate->estimate_no,
            ]);

            DB::commit();
            return redirect()->route('sales.orders.show', $order->id)
                ->with('success', 'Pesanan berhasil dikonfirmasi. Nomor: ' . $orderNo);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Gagal mengkonfirmasi pesanan.']);
        }
    }
}