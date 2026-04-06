<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * v2.6: ai_task_orders テーブル新設
     *
     * AI社員管理モジュールの「指示」親テーブル。
     * 権限者（em_admin / em_hr / em_division_mgr）が出した指示を管理する。
     *
     * ★ 設計原則:
     *   AIは命令主体ではなく「承認済み指示の実行管理主体」である。
     *   AIが処理できるのは approval_status = 'APPROVED' の指示のみ。
     *
     * approval_status:
     *   DRAFT            : 下書き
     *   PENDING_APPROVAL : 承認待ち
     *   APPROVED         : 承認済み（AIが処理可能）
     *   REJECTED         : 却下
     *   CANCELLED        : 取り消し
     *
     * ai_processing_status:
     *   NOT_STARTED : 未処理
     *   ASSIGNED    : 社員へ割当済み
     *   IN_PROGRESS : 進行中
     *   COMPLETED   : 完了
     *   ESCALATED   : エスカレーション中
     *
     * priority_level:
     *   HIGH / MEDIUM / LOW
     *
     * ⚠️ AI禁止事項: 懲戒・減給・解雇・人事評価確定・法的判断
     */
    public function up(): void
    {
        Schema::create('ai_task_orders', function (Blueprint $table) {
            $table->id();

            $table->string('order_no')->unique()
                  ->comment('指示番号（例：ORD-20260001）');

            $table->foreignId('issued_by_user_id')
                  ->constrained('users')
                  ->comment('指示を出した権限者ID（em_admin / em_hr / em_division_mgr）');

            $table->unsignedBigInteger('approver_user_id')->nullable()
                  ->comment('承認者ID（承認フロー使用時）');

            $table->string('instruction_title')
                  ->comment('指示タイトル');

            $table->text('instruction_body')
                  ->comment('指示内容（AIが割当・催促に使用）');

            $table->string('target_division')->nullable()
                  ->comment('対象部署');

            $table->string('priority_level')->default('MEDIUM')
                  ->comment('HIGH / MEDIUM / LOW');

            $table->timestamp('due_at')
                  ->comment('期限');

            $table->string('approval_status')->default('DRAFT')
                  ->comment('DRAFT / PENDING_APPROVAL / APPROVED / REJECTED / CANCELLED');

            $table->string('ai_processing_status')->default('NOT_STARTED')
                  ->comment('NOT_STARTED / ASSIGNED / IN_PROGRESS / COMPLETED / ESCALATED');

            $table->string('visibility_scope')->default('INTERNAL_ONLY')
                  ->comment('INTERNAL_ONLY 固定');

            $table->timestamps();

            // インデックス
            $table->index('order_no');
            $table->index('issued_by_user_id');
            $table->index('approval_status');
            $table->index('ai_processing_status');
            $table->index('due_at');
            $table->index(['approval_status', 'ai_processing_status'],
                          'idx_ato_status');

            // 外部キー
            $table->foreign('approver_user_id')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_task_orders');
    }
};
