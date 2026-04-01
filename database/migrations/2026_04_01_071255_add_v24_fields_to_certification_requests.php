<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certification_requests', function (Blueprint $table) {
            // AI審査トラッキング
            $table->timestamp('ai_review_completed_at')->nullable()->after('ready_for_review');
            $table->string('ai_model_name', 50)->nullable()->after('ai_review_completed_at');
            $table->string('ai_prompt_version', 50)->nullable()->after('ai_model_name');
            $table->unsignedBigInteger('latest_case_review_id')->nullable()->after('ai_prompt_version');

            // Claudeの主要出力指標（v2.4加点方式）
            $table->decimal('base_score', 5, 2)->nullable()->after('weighted_score');
            $table->decimal('truthfulness_percent', 5, 2)->nullable()->after('base_score');
            $table->decimal('consistency_percent', 5, 2)->nullable()->after('truthfulness_percent');
            $table->decimal('hri_suitability_score', 5, 2)->nullable()->after('consistency_percent');
            $table->decimal('work_ability_score', 5, 2)->nullable()->after('hri_suitability_score');
            $table->string('work_ability_band', 20)->nullable()->after('work_ability_score');

            // Claude総合判定
            $table->string('claude_overall_judgment', 80)->nullable()->after('work_ability_band');
            $table->text('claude_overall_reason')->nullable()->after('claude_overall_judgment');
            $table->text('enterprise_view_summary')->nullable()->after('claude_overall_reason');
            $table->string('final_decision', 50)->nullable()->after('enterprise_view_summary');
        });
    }

    public function down(): void
    {
        Schema::table('certification_requests', function (Blueprint $table) {
            $table->dropColumn([
                'ai_review_completed_at',
                'ai_model_name',
                'ai_prompt_version',
                'latest_case_review_id',
                'base_score',
                'truthfulness_percent',
                'consistency_percent',
                'hri_suitability_score',
                'work_ability_score',
                'work_ability_band',
                'claude_overall_judgment',
                'claude_overall_reason',
                'enterprise_view_summary',
                'final_decision',
            ]);
        });
    }
};