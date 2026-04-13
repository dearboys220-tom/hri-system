<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salary_calculation_id')->constrained('salary_calculations')->onDelete('cascade');
            $table->foreignId('staff_user_id')->constrained('users')->onDelete('cascade');
            $table->string('payment_month');
            $table->string('bank_account_name');
            $table->string('bank_name');
            $table->string('bank_account_no');
            $table->decimal('paid_amount', 15, 2);
            $table->string('payment_method')->default('BANK_TRANSFER');
            // BANK_TRANSFER / CASH / QRIS / OTHER
            $table->string('payment_status')->default('SCHEDULED');
            // SCHEDULED / PROCESSED / CONFIRMED / FAILED
            $table->string('payment_reference_no')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->foreignId('processed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_records');
    }
};