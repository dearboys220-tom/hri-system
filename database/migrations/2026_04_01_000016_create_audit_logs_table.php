<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * v2.6: audit_logs テーブル新設
     *
     * 全業務監査証跡テーブル。30種の action_type を定義。
     * case_no 軸で全テーブルを横断検索できる。
     *
     * staff_activity_logs との役割分担:
     *   audit_logs         : 業務全体のイベント（AI処理・結果物発行・承認・企業閲覧）
     *   staff_activity_logs: スタッフ操作の詳細（before/after値の詳細記録）
     *   → 両方を並行して維持する
     *
     * actor_type:
     *   human  : スタッフ・ユーザーの操作
     *   ai     : AI処理による自動操作
     *   system : スケジューラ・システム自動処理
     *
     * action_type 30種（設計書 3.18 参照）:
     *   SIGNUP / CONSENT_GRANTED / CONSENT_WITHDRAWN /
     *   CERT_REQUEST_CREATED / INVESTIGATION_ASSIGNED /
     *   INVESTIGATION_CREATED / INVESTIGATION_UPDATED /
     *   POLICY_VIOLATION_FLAGGED / AI_REVIEW_RUN / AI_PROPOSED /
     *   HUMAN_OVERRIDE / RETURN_CREATED / RETURN_RESOLVED /
     *   HUMAN_REVIEW_ASSIGNED / APPROVED / CONDITIONAL_APPROVED /
     *   REJECTED / VR_PENDING / IR_PENDING / RN_ISSUED /
     *   VR_ISSUED / IR_ISSUED / DELIVERABLE_VOIDED /
     *   DELIVERABLE_EXPIRED / COMPANY_VIEWED / APPLICANT_VIEWED /
     *   EXPORT_REQUESTED / DELETE_REQUESTED /
     *   ACCOUNT_SUSPENDED / SUPER_ADMIN_ACCESS
     */
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();

            $table->string('case_no')->nullable()
                  ->comment('関連案件番号（case_no軸で横断検索可能）');

            $table->unsignedBigInteger('user_id')->nullable()
                  ->comment('操作したユーザーID（システム自動処理時はnull）');

            $table->string('actor_type')
                  ->comment('human / ai / system');

            $table->string('action_type')
                  ->comment('30種の action_type（設計書 3.18 参照）');

            $table->json('old_values_json')->nullable()
                  ->comment('変更前の値');

            $table->json('new_values_json')->nullable()
                  ->comment('変更後の値');

            $table->string('ip_address')->nullable();

            $table->text('user_agent')->nullable();

            $table->string('session_id')->nullable();

            $table->string('request_uri')->nullable()
                  ->comment('リクエストURI');

            $table->string('request_method', 10)->nullable()
                  ->comment('GET / POST / PUT / DELETE');

            $table->string('prompt_version')->nullable()
                  ->comment('AI処理時のプロンプトバージョン');

            $table->string('serial_no')->nullable()
                  ->comment('関連する採番（VR/IR番号等）');

            $table->timestamp('created_at')->useCurrent()
                  ->comment('操作日時（updated_at は持たない）');

            // インデックス
            $table->index('case_no');
            $table->index('user_id');
            $table->index('actor_type');
            $table->index('action_type');
            $table->index('created_at');
            $table->index(['case_no', 'action_type'], 'idx_al_case_action');
            $table->index(['action_type', 'created_at'], 'idx_al_action_date');

            // 外部キー
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
