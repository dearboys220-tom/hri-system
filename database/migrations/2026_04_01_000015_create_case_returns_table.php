<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * v2.6: case_returns テーブル新設
     *
     * 内部差し戻し管理専用テーブル。
     *
     * ⚠️ 最重要: このテーブルの全フィールドは INTERNAL_ONLY。
     *   - 個人会員（applicant）画面への露出：絶対禁止
     *   - 企業会員（company）画面への露出：絶対禁止
     *   - APIレスポンスへの inclusion：絶対禁止
     *   外部には external_status = 'additional_check_in_progress' のみ表示する。
     *
     * return_reason_code 一覧:
     *   PROHIBITION_VIOLATION    : 調査禁止事項違反（conductカテゴリ）
     *   INCOMPLETE_INVESTIGATION : 調査内容が不完全
     *   CONTRADICTION            : 記録の矛盾・不整合
     *   INSUFFICIENT_EVIDENCE    : 証拠書類不足
     *   POLICY_VIOLATION         : その他ポリシー違反
     */
    public function up(): void
    {
        Schema::create('case_returns', function (Blueprint $table) {
            $table->id();

            $table->string('case_no')
                  ->comment('案件番号');

            $table->foreignId('certification_request_id')
                  ->constrained('certification_requests')
                  ->cascadeOnDelete()
                  ->comment('関連認証申請ID');

            $table->unsignedBigInteger('case_review_id')->nullable()
                  ->comment('差し戻しを判定したAI審査ID（case_reviews.id）');

            $table->string('return_reason_code')
                  ->comment('PROHIBITION_VIOLATION / INCOMPLETE_INVESTIGATION / CONTRADICTION / INSUFFICIENT_EVIDENCE / POLICY_VIOLATION');

            $table->text('return_reason_summary')
                  ->comment('差し戻し理由の要約（内部向け・調査部が参照）');

            $table->json('violation_types_json')->nullable()
                  ->comment('違反種別リスト');

            $table->json('error_points_json')->nullable()
                  ->comment('誤記・矛盾箇所リスト');

            $table->json('missing_points_json')->nullable()
                  ->comment('調査不足項目リスト');

            $table->json('incorrect_points_json')->nullable()
                  ->comment('不正確な記録リスト');

            $table->json('reinvestigation_instructions_json')->nullable()
                  ->comment('再調査指示（調査部向け・具体的な確認事項）');

            $table->json('prohibition_warning_json')->nullable()
                  ->comment('禁止事項警告リスト（conductカテゴリ違反時）');

            $table->json('resubmission_requirements_json')->nullable()
                  ->comment('再提出要件リスト');

            $table->timestamp('returned_at')->useCurrent()
                  ->comment('差し戻し日時');

            $table->timestamp('resolved_at')->nullable()
                  ->comment('解消日時（再審査完了時）');

            $table->unsignedBigInteger('resolved_by')->nullable()
                  ->comment('解消した審査管理部スタッフID');

            $table->timestamps();

            // インデックス
            $table->index('case_no');
            $table->index('certification_request_id');
            $table->index('return_reason_code');
            $table->index('resolved_at');

            // 外部キー
            $table->foreign('case_review_id')
                  ->references('id')
                  ->on('case_reviews')
                  ->nullOnDelete();

            $table->foreign('resolved_by')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_returns');
    }
};
