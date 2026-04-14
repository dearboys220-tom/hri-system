<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('campaign_no', 30)->unique()->comment('MKT-YYYYNNNN');
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('campaign_name');
            $table->string('campaign_type', 20)->default('OTHER')
                ->comment('MARKET_RESEARCH/ADVERTISING/SALES_MANAGEMENT/EVENT/OTHER');
            $table->text('target_description')->nullable();
            $table->string('campaign_status', 20)->default('PLANNING')
                ->comment('PLANNING/ACTIVE/ON_HOLD/COMPLETED/CANCELLED');
            $table->text('ai_summary')->nullable();
            $table->decimal('budget', 15, 2)->nullable();
            $table->date('started_at')->nullable();
            $table->date('ended_at')->nullable();
            $table->text('result_report')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_campaigns');
    }
};