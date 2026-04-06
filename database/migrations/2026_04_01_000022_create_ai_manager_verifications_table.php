<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * v2.6: ai_manager_verifications テーブル新設
     *
     * 管理者による現場確認テーブル。
     *
     * ⚠️ 最重要ルール:
     *   現実世界での実行確認は必ず人間管理者が行う。
     *   AIによる verification_result の自動セットは禁止。
     *
     * verification_result:
     *   EXECUTED           : 実行確認済み
     *   PARTIALLY_EXECUTED : 一部実行
     *   NOT_EXECUTED       : 未実行確認
     *   UNCONFIRMED        : 確認できず
     */
    public function up(): void
    {
        Schema::create('ai_manager_verifications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('task_assignment_id')
                  ->constrained('ai_task_assignments')
                  ->cascadeOnDelete()
                  ->comment('関連タスク割当ID');

            $table->foreignId('manager_user_id')
                  ->constrained('users')
                  ->comment('確認した管理者ID（em_admin / em_hr / em_division_mgr）');

            $table->string('verification_result')
                  ->comment('EXECUTED / PARTIALLY_EXECUTED / NOT_EXECUTED / UNCONFIRMED');

            $table->text('verification_comment')->nullable()
                  ->comment('確認コメント（任意）');

            $table->timestamp('verified_at')->useCurrent()
                  ->comment('確認日時');

            $table->timestamps();

            // インデックス
            $table->index('task_assignment_id');
            $table->index('manager_user_id');
            $table->index('verification_result');
            $table->index('verified_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_manager_verifications');
    }
};
