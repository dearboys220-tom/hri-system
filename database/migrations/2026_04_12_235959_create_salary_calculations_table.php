<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_calculations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_user_id')->constrained('users')->onDelete('cascade');
            $table->string('calculation_month');                   // 例: 2026-04
            $table->decimal('base_salary', 15, 2);
            $table->integer('attendance_days')->default(0);
            $table->integer('absent_days')->default(0);
            $table->decimal('task_completion_rate', 5, 2)->nullable();
            $table->string('performance_band')->nullable();
            $table->decimal('performance_adjustment', 15, 2)->default(0);
            $table->decimal('deductions', 15, 2)->default(0);
            $table->decimal('overtime_pay', 15, 2)->default(0);
            $table->decimal('allowances', 15, 2)->default(0);
            $table->decimal('gross_salary', 15, 2)->default(0);
            $table->decimal('net_salary', 15, 2)->default(0);
            $table->string('calculation_status')->default('DRAFT');
            // DRAFT / PENDING_APPROVAL / APPROVED / REJECTED
            $table->text('ai_calculation_notes')->nullable();
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_calculations');
    }
};