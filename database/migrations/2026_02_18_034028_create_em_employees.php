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
        Schema::create('em_employees', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('employee_code')->unique();

            $table->string('full_name');

            $table->string('position')->nullable();
            $table->string('division')->nullable();

            $table->date('join_date')->nullable();

            $table->enum('status', [
                'active',
                'inactive',
                'resigned'
            ])->default('active');

            $table->integer('current_points')->default(0);

            $table->timestamps();

            $table->index(['company_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('em_employees');
    }
};
