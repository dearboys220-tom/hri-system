<?php

namespace App\Jobs;

use App\Services\HriPriorityAnalysisService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RunPriorityAnalysisJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 60;

    public function __construct(private int $certRequestId) {}

    public function handle(HriPriorityAnalysisService $service): void
    {
        Log::info("RunPriorityAnalysisJob: 開始 certRequestId={$this->certRequestId}");
        $report = $service->analyze($this->certRequestId);

        if ($report) {
            Log::info("RunPriorityAnalysisJob: 完了 reportId={$report->id}");
        } else {
            Log::warning("RunPriorityAnalysisJob: レポート生成失敗 certRequestId={$this->certRequestId}");
        }
    }
}