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
        Schema::create('education_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('certification_request_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('level', 50)->nullable();
            $table->string('school')->nullable();
            $table->string('degree')->nullable();
            $table->string('status', 50)->nullable();
            $table->decimal('gpa', 3, 2)->nullable();
            $table->date('enrollment_date')->nullable();
            $table->date('graduation_date')->nullable();
            $table->text('achievements')->nullable();
            $table->string('education_file')->nullable();

            $table->timestamps();
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
