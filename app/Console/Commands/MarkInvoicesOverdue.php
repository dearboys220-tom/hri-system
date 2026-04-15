<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invoice;
use App\Models\AuditLog;
use Carbon\Carbon;

class MarkInvoicesOverdue extends Command
{
    protected $signature   = 'hri:mark-invoices-overdue';
    protected $description = 'Otomatis ubah status invoice yang melewati due_date menjadi overdue';

    public function handle(): int
    {
        $now   = Carbon::now('Asia/Jakarta');
        $today = $now->toDateString();

        // 対象: sent で due_date が今日より前
        $targets = Invoice::where('status', 'sent')
            ->whereNotNull('due_date')
            ->where('due_date', '<', $today)
            ->get();

        if ($targets->isEmpty()) {
            $this->info('[' . $now->toDateTimeString() . '] Tidak ada invoice yang perlu ditandai overdue.');
            return Command::SUCCESS;
        }

        foreach ($targets as $invoice) {
            $invoice->update(['status' => 'overdue']);

            // audit_logs に記録（recordSystem を使用）
            AuditLog::recordSystem(
                'INVOICE_OVERDUE',
                null,
                [
                    'old' => ['status' => 'sent'],
                    'new' => [
                        'status'     => 'overdue',
                        'invoice_id' => $invoice->id,
                        'invoice_no' => $invoice->invoice_no,
                        'due_date'   => $invoice->due_date,
                        'marked_at'  => $now->toDateTimeString(),
                    ],
                ]
            );

            $this->info("  → Overdue: [{$invoice->invoice_no}] due_date={$invoice->due_date}");
        }

        $this->info('[' . $now->toDateTimeString() . '] Total overdue: ' . $targets->count() . ' invoice.');
        return Command::SUCCESS;
    }
}