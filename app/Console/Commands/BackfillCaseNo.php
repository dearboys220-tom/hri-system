<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillCaseNo extends Command
{
    /**
     * php artisan hri:backfill-case-no
     *
     * Migration後に既存データへ case_no を補完するコマンド。
     * Phase 2 Migration 実行直後に一度だけ実行する。
     *
     * 処理内容:
     *   1. certification_requests に case_no がなければ採番して補完
     *   2. 紐づく education_history / work_history / certifications /
     *      investigation_items / review_items にも同じ case_no を補完
     */
    protected $signature   = 'hri:backfill-case-no {--dry-run : 実際には更新せず件数だけ表示}';
    protected $description = 'v2.6: 既存データに case_no を補完する（Phase 2 Migration 後に一度だけ実行）';

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->warn('【DRY RUN モード】実際の更新は行いません。');
        }

        // ① certification_requests の case_no 補完
        $requests = DB::table('certification_requests')
            ->whereNull('case_no')
            ->orderBy('id')
            ->get(['id', 'created_at']);

        $this->info("case_no 未設定の certification_requests: {$requests->count()} 件");

        if ($requests->isEmpty()) {
            $this->info('補完対象なし。スキップします。');
        } else {
            $bar = $this->output->createProgressBar($requests->count());
            $bar->start();

            foreach ($requests as $req) {
                // 採番ルール: CR-WP- は移管データ用（本コマンドでは CR-YYYY 形式で採番）
                $year    = date('Y', strtotime($req->created_at));
                $caseNo  = sprintf('CR-%s%05d', $year, $req->id);

                if (! $isDryRun) {
                    DB::table('certification_requests')
                        ->where('id', $req->id)
                        ->update(['case_no' => $caseNo]);

                    // 紐づく子テーブルにも補完
                    $this->backfillChildren($req->id, $caseNo);
                }

                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
        }

        // ② 子テーブルの case_no 補完（certification_requests は補完済みだが子が未補完の場合）
        $tables = [
            'education_history'   => 'certification_request_id',
            'work_history'        => 'certification_request_id',
            'certifications'      => 'certification_request_id',
            'investigation_items' => 'certification_request_id',
            'review_items'        => 'certification_request_id',
        ];

        foreach ($tables as $table => $fk) {
            $count = DB::table($table)->whereNull('case_no')->count();
            $this->info("{$table}: case_no 未設定 {$count} 件");

            if (! $isDryRun && $count > 0) {
                // certification_requests の case_no を JOIN で補完
                DB::statement("
                    UPDATE {$table} t
                    JOIN certification_requests cr ON cr.id = t.{$fk}
                    SET t.case_no = cr.case_no
                    WHERE t.case_no IS NULL
                      AND cr.case_no IS NOT NULL
                ");
                $this->line("  → {$table} の case_no を補完しました。");
            }
        }

        $this->newLine();
        $this->info($isDryRun ? 'DRY RUN 完了。' : '✅ case_no の補完が完了しました。');

        return self::SUCCESS;
    }

    /**
     * certification_request_id に紐づく子テーブルに case_no を補完
     */
    private function backfillChildren(int $requestId, string $caseNo): void
    {
        $tables = [
            'education_history',
            'work_history',
            'certifications',
            'investigation_items',
            'review_items',
        ];

        foreach ($tables as $table) {
            DB::table($table)
                ->where('certification_request_id', $requestId)
                ->whereNull('case_no')
                ->update(['case_no' => $caseNo]);
        }

        // case_reviews も補完
        DB::table('case_reviews')
            ->where('certification_request_id', $requestId)
            ->whereNull('case_no')
            ->update(['case_no' => $caseNo]);
    }
}
