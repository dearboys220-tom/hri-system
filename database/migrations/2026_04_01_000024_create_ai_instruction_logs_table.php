<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * v2.6: ai_instruction_logs テーブル新設
     *
     * 指示から完了までの全操作ログテーブル。
     * 人間・AI・システムの全アクションを時系列で記録する。
     *
     * actor_type:
     *   human  : 人間（管理者・社員）の操作
     *   ai     : AI による自動処理
     *   system : スケジューラ等のシステム自動処理
     *
     * action_type 10種:
     *   ORDER_CREATED        : 指示作成
     *   ORDER_APPROVED       : 指示承認
     *   AI_ASSIGNED          : AI割当実施
     *   EMPLOYEE_ACKNOWLEDGED: 社員が確認
     *   EMPLOYEE_REPORTED    : 社員が報告
     *   AI_SUMMARIZED        : AI要約実施
     *   MANAGER_VERIFIED     : 管理者が現場確認
     *   ESCALATED            : エスカレーション発生
     *   RESOLVED             : 解消
     *   CLOSED               : 完了・クローズ
     */
    public function up(): void
    {
        Schema::create('ai_instruction_logs', function (Blueprint $table) {
            $table->id();

            $table->string('order_no')
                  ->comment('指示番号（ai_task_orders.order_no）');

            $table->unsignedBigInteger('task_assignment_id')->nullable()
                  ->comment('関連タスク割当ID（社員個別の操作時）');

            $table->string('actor_type')
                  ->comment('human / ai / system');

            $table->unsignedBigInteger('actor_user_id')->nullable()
                  ->comment('操作者ID（AI処理時はnull）');

            $table->string('action_type')
                  ->comment('ORDER_CREATED / ORDER_APPROVED / AI_ASSIGNED / EMPLOYEE_ACKNOWLEDGED / EMPLOYEE_REPORTED / AI_SUMMARIZED / MANAGER_VERIFIED / ESCALATED / RESOLVED / CLOSED');

            $table->text('action_summary')->nullable()
                  ->comment('操作サマリー（AIが生成 or 人間が入力）');

            $table->timestamp('created_at')->useCurrent()
                  ->comment('操作日時（updated_at は持たない）');

            // インデックス
            $table->index('order_no');
            $table->index('task_assignment_id');
            $table->index('actor_type');
            $table->index('action_type');
            $table->index('created_at');

            // 外部キー
            $table->foreign('task_assignment_id')
                  ->references('id')
                  ->on('ai_task_assignments')
                  ->nullOnDelete();

            $table->foreign('actor_user_id')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_instruction_logs');
    }
};
