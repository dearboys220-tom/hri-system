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
        Schema::create('em_points', function (Blueprint $table) {
            $table->id();

            $table->foreignId('employee_id')
                ->constrained('em_employees')
                ->cascadeOnDelete();

            $table->enum('type', [
                'earned',
                'redeemed',
                'adjusted'
            ]);

            $table->integer('points');

            $table->string('reason');

            $table->foreignId('given_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['employee_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('em_points');
    }
};
