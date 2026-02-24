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
        Schema::create('em_subscriptions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('plan_id')
                ->constrained('em_plans')
                ->restrictOnDelete();

            $table->enum('status', [
                'active',
                'expired',
                'suspended',
                'cancelled'
            ])->default('active');

            $table->boolean('is_gifted')->default(true);

            $table->foreignId('gifted_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('gifted_reason')->nullable();

            $table->date('start_date');

            $table->date('end_date')->nullable();

            $table->date('next_billing_date')->nullable();

            $table->foreignId('payment_id')
                ->nullable()
                ->constrained('payments')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index('end_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('em_subscriptions');
    }
};
