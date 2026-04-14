<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('strategy_cases', function (Blueprint $table) {
            $table->id();
            $table->string('case_no', 30)->unique()->comment('ST-YYYYNNNN');
            $table->foreignId('client_company_id')->nullable()->constrained('company_profiles')->nullOnDelete();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('case_type', 30)->default('OTHER')
                ->comment('LABOR_LAW/CONTRACT/COMPLIANCE/BANKRUPTCY/DISPUTE/OTHER');
            $table->string('case_title');
            $table->text('case_description')->nullable();
            $table->boolean('requires_registered_lawyer')->default(false);
            $table->foreignId('lawyer_contract_id')->nullable();
            $table->string('case_status', 20)->default('OPEN')
                ->comment('OPEN/IN_PROGRESS/PENDING_LAWYER/RESOLVED/CLOSED/ESCALATED');
            $table->string('risk_level', 10)->default('LOW')->comment('HIGH/MEDIUM/LOW');
            $table->text('ai_risk_summary')->nullable();
            $table->boolean('human_review_required')->default(false);
            $table->text('resolution_summary')->nullable();
            $table->string('billing_status', 10)->default('UNBILLED')->comment('UNBILLED/BILLED/PAID');
            $table->decimal('fee_amount', 15, 2)->nullable();
            $table->date('started_at')->nullable();
            $table->date('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('strategy_cases');
    }
};