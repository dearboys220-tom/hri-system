<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Estimate;
use App\Models\AuditLog;
use Carbon\Carbon;

class ExpireEstimates extends Command
{
    protected $signature   = 'hri:expire-estimates';
    protected $description = 'Otomatis ubah status estimasi yang melewati valid_until menjadi expired';

    public function handle(): int
    {
        $now   = Carbon::now('Asia/Jakarta');
        $today = $now->toDateString();

        // 対象: sent または approved で valid_until が今日より前
        $targets = Estimate::whereIn('status', ['sent', 'approved'])
            ->whereNotNull('valid_until')
            ->where('valid_until', '<', $today)
            ->get();

        if ($targets->isEmpty()) {
            $this->info('[' . $now->toDateTimeString() . '] Tidak ada estimasi yang perlu diexpire.');
            return Command::SUCCESS;
        }

        foreach ($targets as $estimate) {
            $oldStatus = $estimate->status;

            $estimate->update([
                'status'     => 'expired',
                'expired_at' => $now,
            ]);

            // audit_logs に記録（recordSystem を使用）
            AuditLog::recordSystem(
                'ESTIMATE_EXPIRED',
                null,
                [
                    'old' => ['status' => $oldStatus],
                    'new' => [
                        'status'      => 'expired',
                        'expired_at'  => $now->toDateTimeString(),
                        'estimate_id' => $estimate->id,
                        'estimate_no' => $estimate->estimate_no,
                        'valid_until' => $estimate->valid_until,
                    ],
                ]
            );

            $this->info("  → Expired: [{$estimate->estimate_no}] valid_until={$estimate->valid_until}");
        }

        $this->info('[' . $now->toDateTimeString() . '] Total expired: ' . $targets->count() . ' estimasi.');
        return Command::SUCCESS;
    }
}