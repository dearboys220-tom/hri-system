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
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('job_post_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('applicant_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('company_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('status', 50);
            $table->timestamp('applied_at')->nullable();
            $table->timestamp('status_updated_at')->nullable();

            $table->json('applicant_snapshot')->nullable();
            $table->boolean('job_deleted')->default(false);
            $table->timestamp('job_deleted_date')->nullable();

            $table->timestamps();

            $table->unique(['job_post_id', 'applicant_id']);
            $table->index(['company_id', 'status']);
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
