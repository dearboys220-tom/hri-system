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
        Schema::table('company_profiles', function (Blueprint $table) {
            $table->string('company_logo')->nullable()->after('company_name');
            $table->string('company_website')->nullable()->after('company_email');
            $table->text('company_description')->nullable()->after('company_address');
            $table->string('industry_type', 100)->nullable()->after('company_description');
            $table->string('company_size', 50)->nullable()->after('industry_type');

            $table->enum('company_verification_status', [
                'pending', 
                'verified', 
                'suspended', 
                'rejected'
            ])->default('pending')->after('company_size');

            $table->timestamp('verified_at')->nullable()->after('company_verification_status');
            $table->foreignId('verified_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->after('verified_at');

            $table->text('verification_notes')->nullable()->after('verified_by');$table->boolean('free_job_post_used')->after('verification_notes');
            $table->timestamp('free_job_post_expires_at')->nullable()->after('free_job_post_used');
            $table->json('purchased_score_details')->nullable()->after('verification_notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_profiles', function (Blueprint $table) {
            //
        });
    }
};
