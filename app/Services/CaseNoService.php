<?php

namespace App\Services;

use App\Models\CertificationRequest;
use Illuminate\Support\Facades\DB;

class CaseNoService
{
    /**
     * ============================================================
     * CaseNoService
     *
     * case_no の採番を一元管理する。
     *
     * 採番ルール:
     *   通常申請: CR-YYYYNNNNN（例: CR-202600001）
     *   WP移管:   CR-WP-NNNNN（例: CR-WP-00001）
     *
     * 同時申請による採番競合を防ぐため、
     * DB::transaction + lockForUpdate() で排他制御する。
     * ============================================================
     */

    /**
     * 新規申請用の case_no を採番して返す。
     * 競合防止のため排他ロックをかけて採番する。
     */
    public function generate(): string
    {
        return DB::transaction(function () {

            $year = now()->year;

            // 今年の最大採番を排他ロック付きで取得
            $max = CertificationRequest::whereNotNull('case_no')
                ->where('case_no', 'LIKE', "CR-{$year}%")
                ->where('case_no', 'NOT LIKE', 'CR-WP-%')
                ->lockForUpdate()
                ->max(DB::raw("CAST(SUBSTRING(case_no, 8) AS UNSIGNED)"));

            $next = ($max ?? 0) + 1;

            return sprintf('CR-%d%05d', $year, $next);
        });
    }

    /**
     * WP移管データ用の case_no を採番して返す。
     * MigrateFromWordpress コマンド内でのみ使用する。
     */
    public function generateForWordpress(): string
    {
        return DB::transaction(function () {

            $max = CertificationRequest::whereNotNull('case_no')
                ->where('case_no', 'LIKE', 'CR-WP-%')
                ->lockForUpdate()
                ->max(DB::raw("CAST(SUBSTRING(case_no, 7) AS UNSIGNED)"));

            $next = ($max ?? 0) + 1;

            return sprintf('CR-WP-%05d', $next);
        });
    }

    /**
     * certification_requests 作成時に自動採番して付与する。
     * CertificationRequest::creating イベントで呼び出す想定。
     *
     * 使用例（CertificationRequest の boot メソッド）:
     *   static::creating(function ($request) {
     *       if (empty($request->case_no)) {
     *           $request->case_no = app(CaseNoService::class)->generate();
     *       }
     *   });
     */
    public function assignTo(CertificationRequest $request): void
    {
        if (empty($request->case_no)) {
            $request->case_no = $this->generate();
        }
    }

    /**
     * case_no の形式が有効かチェックする。
     */
    public function isValid(string $caseNo): bool
    {
        // CR-YYYYNNNNN 形式
        if (preg_match('/^CR-\d{4}\d{5}$/', $caseNo)) {
            return true;
        }

        // CR-WP-NNNNN 形式（WP移管データ）
        if (preg_match('/^CR-WP-\d{5}$/', $caseNo)) {
            return true;
        }

        return false;
    }
}
