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
        Schema::table('payments', function (Blueprint $table) {
            $table->string('midtrans_order_id')->nullable()->unique()->after('transaction_id');
            $table->string('midtrans_transaction_id')->nullable()->after('midtrans_order_id');
            $table->string('midtrans_snap_token')->nullable()->after('midtrans_transaction_id');
            $table->boolean('is_free')->after('midtrans_snap_token');

            $table->enum('payment_type', ['certification', 'job_post', 'score_detail'])->after('payment_status');

            $table->string('target_member_id', 20)->nullable()->after('payment_type');

            $table->foreignId('related_certification_id')->nullable()->constrained('certification_requests')->nullOnDelete()->after('target_member_id');

            $table->foreignId('related_job_post_id')->nullable()->constrained('job_posts')->nullOnDelete()->after('related_certification_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            //
        });
    }
};
