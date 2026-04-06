<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * v2.6: ai_employee_reports テーブル新設
     *
     * 社員からの業務報告テーブル。
     * AIが report_body を要約し ai_summary に保存、上長向けに再整理する。
     *
     * ルール:
     *   - 社員は自己タスク（自分が assigned された task_assignment）に対してのみ報告可能
     *   - AIが report_body を読み取り → ai_summary を生成
     *   - 矛盾検知時は inconsistency_flag = true をセット → エスカレーション候補
     */
    public function up(): void
    {
        Schema::create('ai_employee_reports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('task_assignment_id')
                  ->constrained('ai_task_assignments')
                  ->cascadeOnDelete()
                  ->comment('関連タスク割当ID');

            $table->foreignId('employee_user_id')
                  ->constrained('users')
                  ->comment('報告した社員ID');

            $table->text('report_body')
                  ->comment('社員が入力した報告内容');

            $table->text('ai_summary')->nullable()
                  ->comment('AIが上長向けに要約した内容');

            $table->boolean('evidence_attached_flag')->default(false)
                  ->comment('証拠ファイル添付の有無');

            $table->boolean('inconsistency_flag')->default(false)
                  ->comment('AIが矛盾を検知したか（trueの場合エスカレーション候補）');

            $table->timestamp('reported_at')->useCurrent()
                  ->comment('報告日時');

            $table->timestamps();

            // インデックス
            $table->index('task_assignment_id');
            $table->index('employee_user_id');
            $table->index('inconsistency_flag');
            $table->index('reported_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_employee_reports');
    }
};
