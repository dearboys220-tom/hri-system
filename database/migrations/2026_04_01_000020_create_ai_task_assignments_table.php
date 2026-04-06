<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * v2.6: ai_task_assignments テーブル新設
     *
     * ai_task_orders（指示）に対する社員別割当テーブル。
     * 1つの指示に対して複数の社員へ割り当て可能。
     *
     * task_status:
     *   ASSIGNED     : 割当済み（社員未確認）
     *   ACKNOWLEDGED : 社員が確認済み
     *   IN_PROGRESS  : 作業中
     *   COMPLETED    : 完了
     *   DELAYED      : 遅延（期限超過でAIが自動セット）
     *   FAILED       : 失敗・未実行
     *   ESCALATED    : エスカレーション中
     */
    public function up(): void
    {
        Schema::create('ai_task_assignments', function (Blueprint $table) {
            $table->id();

            $table->string('order_no')
                  ->comment('指示番号（ai_task_orders.order_no）');

            $table->foreignId('task_order_id')
                  ->constrained('ai_task_orders')
                  ->cascadeOnDelete()
                  ->comment('関連指示ID');

            $table->foreignId('employee_user_id')
                  ->constrained('users')
                  ->comment('割当対象社員ID');

            $table->timestamp('assigned_by_ai_at')->nullable()
                  ->comment('AI割当日時');

            $table->text('assignment_summary')->nullable()
                  ->comment('AIが生成した個人向け割当サマリー');

            $table->string('task_status')->default('ASSIGNED')
                  ->comment('ASSIGNED / ACKNOWLEDGED / IN_PROGRESS / COMPLETED / DELAYED / FAILED / ESCALATED');

            $table->timestamp('started_at')->nullable()
                  ->comment('作業開始日時');

            // timestamp NOT NULL はMySQL strict modeでエラーになるため
            // datetime nullable に変更（アプリ側でバリデーション必須）
            $table->dateTime('due_at')->nullable()
                  ->comment('期限（ai_task_orders.due_at から引き継ぐ・必須）');

            $table->timestamp('completed_at')->nullable()
                  ->comment('完了日時');

            $table->boolean('delay_flag')->default(false)
                  ->comment('遅延フラグ（期限超過でAIが自動セット）');

            $table->timestamps();

            // インデックス
            $table->index('order_no');
            $table->index('task_order_id');
            $table->index('employee_user_id');
            $table->index('task_status');
            $table->index('delay_flag');
            $table->index('due_at');
            $table->index(['employee_user_id', 'task_status'],
                          'idx_ata_employee_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_task_assignments');
    }
};
