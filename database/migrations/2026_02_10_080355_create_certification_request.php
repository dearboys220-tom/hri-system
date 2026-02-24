<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('certification_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('survey_status', 50);
            $table->string('investigation_status', 50)->nullable();

            $table->decimal('evaluation_score', 5, 2)->nullable();
            $table->decimal('weighted_score', 5, 2)->nullable();
            $table->decimal('total_deductions', 5, 2)->nullable();

            $table->timestamp('request_date')->nullable();
            $table->timestamp('final_approval_date')->nullable();

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('rejected_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('rejection_reason')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'survey_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
