<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Invoice;
use App\Models\AuditLog;
use App\Services\NumberingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class OrderController extends Controller
{
    public function __construct(private NumberingService $numbering) {}

    // ── 一覧
    public function index()
    {
        $orders = Order::with(['estimate', 'createdBy', 'invoice'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return Inertia::render('Sales/Orders/Index', compact('orders'));
    }

    // ── 詳細
    public function show(Order $order)
    {
        $order->load(['estimate', 'createdBy', 'approvedBy', 'invoice']);
        return Inertia::render('Sales/Orders/Show', compact('order'));
    }

    // ── 請求書作成（Section 30.4: 受注確定後のみ）
    public function createInvoice(Order $order)
    {
        if ($order->invoice) {
            return redirect()->route('sales.invoices.show', $order->invoice->id)
                ->with('info', 'Faktur sudah dibuat.');
        }

        DB::beginTransaction();
        try {
            $year      = Carbon::now('Asia/Jakarta')->year;
            $invoiceNo = $this->numbering->generate('INV', 'SALES', $year);

            $invoice = Invoice::create([
                'invoice_no'         => $invoiceNo,
                'order_id'           => $order->id,
                'estimate_id'        => $order->estimate_id,
                'client_name'        => $order->client_name,
                'client_email'       => $order->client_email,
                'service_type'       => $order->service_type,
                'subtotal'           => $order->estimate->subtotal,
                'discount_amount'    => $order->estimate->discount_amount,
                'final_amount'       => $order->final_amount,
                'tax_note'           => $order->estimate->tax_note,
                'payment_terms'      => $order->payment_terms,
                'due_date'           => Carbon::now('Asia/Jakarta')->addDays(14)->toDateString(),
                'status'             => Invoice::STATUS_DRAFT,
                'created_by_user_id' => Auth::id(),
            ]);

            AuditLog::create([
                'user_id'     => Auth::id(),
                'action_type' => 'NUMBER_ISSUED',
                'target_type' => 'invoices',
                'target_id'   => $invoice->id,
                'new_value'   => json_encode(['invoice_no' => $invoiceNo]),
                'notes'       => 'Nomor faktur diterbitkan',
            ]);

            DB::commit();
            return redirect()->route('sales.invoices.show', $invoice->id)
                ->with('success', 'Faktur berhasil dibuat. Nomor: ' . $invoiceNo);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Gagal membuat faktur.']);
        }
    }

    // ── 完了
    public function complete(Order $order)
    {
        $order->update([
            'status'       => Order::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);

        AuditLog::create([
            'user_id'     => Auth::id(),
            'action_type' => 'ORDER_COMPLETED',
            'target_type' => 'orders',
            'target_id'   => $order->id,
            'notes'       => 'Pesanan selesai',
        ]);

        return redirect()->back()->with('success', 'Pesanan ditandai selesai.');
    }
}