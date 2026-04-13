<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_absence_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_user_id')->constrained('users')->onDelete('cascade');
            $table->string('absence_type');
            // SICK / PERSONAL / ANNUAL_LEAVE / EMERGENCY / OTHER
            $table->date('absence_date_from');
            $table->date('absence_date_to');
            $table->integer('absence_days');
            $table->text('reason')->nullable();
            $table->string('supporting_doc_path')->nullable(); // 診断書等
            $table->string('approval_status')->default('PENDING');
            // PENDING / APPROVED / REJECTED
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_absence_requests');
    }
};