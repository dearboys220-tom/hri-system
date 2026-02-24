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
        Schema::table('certification_requests', function (Blueprint $table) {
            $table->foreignId('assigned_investigator')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()->after('user_id');

            $table->timestamp('investigation_started_at')->nullable()->after('investigation_status');
            $table->timestamp('investigation_completed_at')->nullable()->after('investigation_started_at');
            $table->text('investigation_notes')->nullable()->after('investigation_completed_at');
            $table->boolean('ready_for_review')->default(false)->after('investigation_notes');

            $table->foreignId('assigned_reviewer')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->after('ready_for_review');

            $table->timestamp('review_started_at')->nullable()->after('assigned_reviewer');
            $table->timestamp('review_completed_at')->nullable()->after('review_started_at');
            $table->text('reviewer_comments')->nullable()->after('total_deductions');
            $table->timestamp('review_completed_date')->nullable()->after('reviewer_comments');

            $table->boolean('admin_approved')->default(false)->after('final_approval_date');
            $table->timestamp('admin_approval_date')->nullable()->after('admin_approved');
            $table->text('admin_notes')->nullable()->after('approved_by');

            $table->boolean('returned_to_applicant')->default(false)->after('rejection_reason');
            $table->text('return_reason')->nullable()->after('returned_to_applicant');

            $table->foreignId('payment_id')
                ->nullable()
                ->constrained('payments')
                ->nullOnDelete()
                ->after('return_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certification_requests', function (Blueprint $table) {
            //
        });
    }
};
