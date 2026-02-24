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
        Schema::create('em_attendance', function (Blueprint $table) {
            $table->id();

            $table->foreignId('employee_id')
                ->constrained('em_employees')
                ->cascadeOnDelete();

            $table->date('date');

            $table->timestamp('check_in')->nullable();
            $table->timestamp('check_out')->nullable();

            $table->decimal('check_in_lat', 10, 7)->nullable();
            $table->decimal('check_in_lng', 10, 7)->nullable();
            $table->decimal('check_out_lat', 10, 7)->nullable();
            $table->decimal('check_out_lng', 10, 7)->nullable();

            $table->enum('status', [
                'present',
                'late',
                'absent',
                'leave'
            ])->default('present');

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(['employee_id', 'date']);

            $table->index(['date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('em_attendance');
    }
};
