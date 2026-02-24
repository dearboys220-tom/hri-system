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
        Schema::create('certifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('certification_request_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('organization')->nullable();
            $table->date('issued_date')->nullable();
            $table->date('valid_until')->nullable();
            $table->string('certificate_file')->nullable();
            $table->text('notes')->nullable();

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
