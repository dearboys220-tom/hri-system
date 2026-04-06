<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * v2.6: ai_escalations テーブル新設
     *
     * 遅延・未報告・未実行・矛盾・拒否のエスカレーション管理テーブル。
     * AIが自動検知し、管理者・上長に通知する。
     *
     * escalation_type:
     *   DELAY         : 期限超過（delay_flag = true）
     *   NO_REPORT     : 報告未提出
     *   NOT_EXECUTED  : 現場確認で未実行が判明
     *   INCONSISTENCY : 報告内容の矛盾（inconsistency_flag = true）
     *   REFUSAL       : 業務拒否
     *
     * status:
     *   OPEN         : 未対応
     *   ACKNOWLEDGED : 管理者が確認済み
     *   RESOLVED     : 解消済み
     *   CLOSED       : クローズ
     */
    public function up(): void
    {
        Schema::create('ai_escalations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('task_assignment_id')
                  ->constrained('ai_task_assignments')
                  ->cascadeOnDelete()
                  ->comment('関連タスク割当ID');

            $table->string('escalation_type')
                  ->comment('DELAY / NO_REPORT / NOT_EXECUTED / INCONSISTENCY / REFUSAL');

            $table->text('escalation_reason')
                  ->comment('エスカレーション理由（AIが生成）');

            $table->foreignId('reported_to_user_id')
                  ->constrained('users')
                  ->comment('報告先（管理者・上長）のユーザーID');

            $table->string('status')->default('OPEN')
                  ->comment('OPEN / ACKNOWLEDGED / RESOLVED / CLOSED');

            $table->dateTime('escalated_at')->nullable()
                  ->comment('エスカレーション発生日時');

            $table->timestamp('resolved_at')->nullable()
                  ->comment('解消日時');

            $table->timestamps();

            // インデックス
            $table->index('task_assignment_id');
            $table->index('escalation_type');
            $table->index('reported_to_user_id');
            $table->index('status');
            $table->index('escalated_at');
            $table->index(['status', 'escalated_at'], 'idx_ae_status_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_escalations');
    }
};
