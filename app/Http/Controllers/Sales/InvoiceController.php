<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class InvoiceController extends Controller
{
    // ── 一覧
    public function index()
    {
        $invoices = Invoice::with(['order', 'createdBy'])
            ->orderByDesc('created_at')
            ->paginate(20);

        $stats = [
            'draft'            => Invoice::where('status', 'draft')->count(),
            'pending_approval' => Invoice::where('status', 'pending_approval')->count(),
            'sent'             => Invoice::where('status', 'sent')->count(),
            'paid'             => Invoice::where('status', 'paid')->count(),
            'overdue'          => Invoice::where('status', 'overdue')
                                         ->orWhere(function ($q) {
                                             $q->where('status', 'sent')
                                               ->where('due_date', '<', now());
                                         })->count(),
        ];

        return Inertia::render('Sales/Invoices/Index', compact('invoices', 'stats'));
    }

    // ── 詳細
    public function show(Invoice $invoice)
    {
        $invoice->load(['order', 'estimate', 'createdBy', 'approvedBy']);
        return Inertia::render('Sales/Invoices/Show', compact('invoice'));
    }

    // ── 承認申請
    public function submitForApproval(Invoice $invoice)
    {
        $invoice->update(['status' => Invoice::STATUS_PENDING_APPROVAL]);

        AuditLog::create([
            'user_id'     => Auth::id(),
            'action_type' => 'INVOICE_SUBMITTED',
            'target_type' => 'invoices',
            'target_id'   => $invoice->id,
            'notes'       => 'Faktur diajukan untuk persetujuan',
        ]);

        return redirect()->back()->with('success', 'Faktur diajukan untuk persetujuan.');
    }

    // ── 承認
    public function approve(Invoice $invoice)
    {
        $invoice->update([
            'status'             => Invoice::STATUS_APPROVED,
            'approved_by_user_id' => Auth::id(),
            'approved_at'        => now(),
        ]);

        AuditLog::create([
            'user_id'     => Auth::id(),
            'action_type' => 'INVOICE_APPROVED',
            'target_type' => 'invoices',
            'target_id'   => $invoice->id,
            'notes'       => 'Faktur disetujui',
        ]);

        return redirect()->back()->with('success', 'Faktur disetujui.');
    }

    // ── 送付済み
    public function markSent(Invoice $invoice)
    {
        $invoice->update([
            'status'  => Invoice::STATUS_SENT,
            'sent_at' => now(),
        ]);

        AuditLog::create([
            'user_id'     => Auth::id(),
            'action_type' => 'INVOICE_SENT',
            'target_type' => 'invoices',
            'target_id'   => $invoice->id,
            'notes'       => 'Faktur dikirim ke klien',
        ]);

        return redirect()->back()->with('success', 'Faktur ditandai sebagai terkirim.');
    }

    // ── 入金確認
    public function markPaid(Request $request, Invoice $invoice)
    {
        $request->validate([
            'payment_method' => 'required|string',
            'payment_notes'  => 'nullable|string',
        ]);

        $invoice->update([
            'status'         => Invoice::STATUS_PAID,
            'paid_at'        => now(),
            'payment_method' => $request->payment_method,
            'payment_notes'  => $request->payment_notes,
        ]);

        AuditLog::create([
            'user_id'     => Auth::id(),
            'action_type' => 'INVOICE_PAID',
            'target_type' => 'invoices',
            'target_id'   => $invoice->id,
            'notes'       => '入金確認: ' . $request->payment_method,
        ]);

        return redirect()->back()->with('success', 'Pembayaran berhasil dikonfirmasi.');
    }
}