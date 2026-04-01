<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('case_reviews', function (Blueprint $table) {
            $table->id();

            $table->foreignId('certification_request_id')
                ->constrained()
                ->cascadeOnDelete();

            // AI審査メタ情報
            $table->string('prompt_version', 50)->nullable();
            $table->string('model_name', 50)->nullable();
            $table->json('raw_response_json')->nullable();

            // 最終判定
            $table->string('final_decision', 50);
            // APPROVE / CONDITIONAL_APPROVE / REJECT / ESCALATE_TO_HUMAN / RETURN_TO_INVESTIGATION

            // スコア指標（加点方式・100点満点）
            $table->decimal('base_score', 5, 2)->nullable();
            $table->decimal('truthfulness_percent', 5, 2)->nullable();
            $table->decimal('consistency_percent', 5, 2)->nullable();
            $table->decimal('hri_suitability_score', 5, 2)->nullable();
            $table->decimal('work_ability_score', 5, 2)->nullable();
            $table->string('work_ability_band', 20)->nullable();
            // HIGH / MODERATE / LIMITED / LOW

            // Claude総合判断
            $table->string('claude_overall_judgment', 80)->nullable();
            $table->text('claude_overall_reason')->nullable();
            $table->text('enterprise_view_summary')->nullable();
            $table->text('summary')->nullable();

            // JSON形式の詳細データ
            $table->json('verified_items_json')->nullable();
            $table->json('unverified_items_json')->nullable();
            $table->json('risk_flags_json')->nullable();
            $table->json('conditions_json')->nullable();
            $table->json('compliance_return_json')->nullable();

            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['certification_request_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_reviews');
    }
};