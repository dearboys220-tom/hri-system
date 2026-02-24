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
        Schema::create('review_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('certification_request_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('category', 50);
            $table->string('item_name', 100);

            $table->decimal('max_deduction', 5, 2);
            $table->decimal('actual_deduction', 5, 2)->default(0);
            $table->decimal('weight', 3, 2);

            $table->text('notes')->nullable();

            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['certification_request_id', 'category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('review_items');
    }
};
