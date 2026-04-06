<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * v2.6: certification_requests.latest_return_id に外部キーを追加
     *
     * Phase 2 では case_returns が存在しなかったため外部キーを設定できなかった。
     * Phase 3 で case_returns を作成した後にこの Migration で外部キーを追加する。
     *
     * ⚠️ このMigrationは必ず _000015（create_case_returns_table）の後に実行する。
     */
    public function up(): void
    {
        Schema::table('certification_requests', function (Blueprint $table) {
            $table->foreign('latest_return_id')
                  ->references('id')
                  ->on('case_returns')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('certification_requests', function (Blueprint $table) {
            $table->dropForeign(['latest_return_id']);
        });
    }
};
