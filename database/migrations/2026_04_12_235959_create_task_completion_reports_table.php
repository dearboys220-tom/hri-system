<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_completion_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_order_id')->constrained('ai_task_orders')->onDelete('cascade');
            $table->string('report_no')->unique();               // RPT-20260001
            $table->boolean('generated_by_ai')->default(true);
            $table->string('report_title');
            $table->text('execution_summary');
            $table->text('result_summary');
            $table->decimal('completion_rate', 5, 2)->nullable();
            $table->boolean('on_time_flag')->default(false);
            $table->json('assignee_evaluations_json')->nullable(); // 担当者別評価配列
            $table->text('issues_identified')->nullable();
            $table->text('recommendations')->nullable();
            $table->foreignId('reported_to_user_id')->constrained('users')->onDelete('cascade');
            $table->string('report_status')->default('DRAFT');
            // DRAFT / DELIVERED / ACKNOWLEDGED
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_completion_reports');
    }
};