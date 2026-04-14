<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_dev_projects', function (Blueprint $table) {
            $table->id();
            $table->string('project_no', 30)->unique()->comment('AID-YYYYNNNN');
            $table->foreignId('client_company_id')->nullable()->constrained('company_profiles')->nullOnDelete();
            $table->foreignId('lead_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('project_name');
            $table->string('project_type', 20)->default('OTHER')
                ->comment('CONSULTATION/DESIGN/DEVELOPMENT/DELIVERY/MAINTENANCE/OTHER');
            $table->text('project_description')->nullable();
            $table->string('project_status', 20)->default('PROPOSAL')
                ->comment('PROPOSAL/CONTRACTED/IN_PROGRESS/TESTING/DELIVERED/MAINTENANCE/COMPLETED/CANCELLED');
            $table->text('ai_progress_summary')->nullable();
            $table->decimal('contract_amount', 15, 2)->nullable();
            $table->string('billing_status', 10)->default('UNBILLED')->comment('UNBILLED/BILLED/PAID');
            $table->date('started_at')->nullable();
            $table->date('delivery_due_at')->nullable();
            $table->date('delivered_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_dev_projects');
    }
};