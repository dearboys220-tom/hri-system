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
        Schema::create('investigation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('certification_request_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('category', 50);
            $table->string('item_name', 100);
            $table->enum('validity', ['VALID', 'INVALID']);
            $table->text('notes')->nullable();

            $table->unsignedBigInteger('checked_by')->nullable();
            $table->foreign('checked_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->timestamp('checked_at')->nullable();
            $table->timestamps();

            $table->index(['certification_request_id', 'category']);
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
