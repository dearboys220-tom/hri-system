<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('task_order_id')->nullable()->constrained('ai_task_orders')->nullOnDelete();
            $table->date('evaluation_period_from');
            $table->date('evaluation_period_to');
            $table->string('evaluation_type');
            // TASK_BASED / MONTHLY / QUARTERLY
            $table->string('ai_performance_band')->nullable();
            // GOOD / FAIR / WARNING
            $table->decimal('ai_score', 5, 2)->nullable();
            $table->text('ai_evaluation_summary')->nullable();
            $table->json('ai_evaluation_detail')->nullable();
            $table->string('ai_recommended_action')->nullable();
            // COMMEND / MONITOR / WARNING_DRAFT
            $table->boolean('warning_draft_triggered')->default(false);
            $table->string('human_final_band')->nullable();       // 管理者確定値
            $table->text('human_override_reason')->nullable();
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('salary_calculation_id')->nullable(); // 後でFK追加
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_evaluations');
    }
};