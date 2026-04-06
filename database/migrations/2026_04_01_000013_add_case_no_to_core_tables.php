<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * v2.6: コアテーブル群への case_no 追加
     *
     * 対象テーブル（5本まとめて追加）:
     *   - education_history
     *   - work_history
     *   - certifications
     *   - investigation_items
     *   - review_items
     *
     * case_no は certification_requests.case_no と同じ値を持つ。
     * 外部キー制約はつけず、インデックスのみ追加（検索・監査用）。
     *
     * ⚠️ 既存データへの case_no 補完は別途 Seeder または
     *    php artisan tinker で実行すること（後述）。
     */
    public function up(): void
    {
        // ① education_history
        Schema::table('education_history', function (Blueprint $table) {
            $table->string('case_no')->nullable()
                  ->after('id')
                  ->comment('案件番号（certification_requests.case_no と一致）');
            $table->index('case_no');
        });

        // ② work_history
        Schema::table('work_history', function (Blueprint $table) {
            $table->string('case_no')->nullable()
                  ->after('id')
                  ->comment('案件番号（certification_requests.case_no と一致）');
            $table->index('case_no');
        });

        // ③ certifications（資格テーブル）
        Schema::table('certifications', function (Blueprint $table) {
            $table->string('case_no')->nullable()
                  ->after('id')
                  ->comment('案件番号（certification_requests.case_no と一致）');
            $table->index('case_no');
        });

        // ④ investigation_items
        Schema::table('investigation_items', function (Blueprint $table) {
            $table->string('case_no')->nullable()
                  ->after('id')
                  ->comment('案件番号（certification_requests.case_no と一致）');
            $table->index('case_no');
        });

        // ⑤ review_items
        Schema::table('review_items', function (Blueprint $table) {
            $table->string('case_no')->nullable()
                  ->after('id')
                  ->comment('案件番号（certification_requests.case_no と一致）');
            $table->index('case_no');
        });
    }

    public function down(): void
    {
        $tables = [
            'education_history',
            'work_history',
            'certifications',
            'investigation_items',
            'review_items',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropIndex(['case_no']);
                $blueprint->dropColumn('case_no');
            });
        }
    }
};
