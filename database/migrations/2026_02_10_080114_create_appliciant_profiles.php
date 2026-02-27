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
        Schema::create('applicant_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('full_name', 50)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('gender', 20)->nullable();
            $table->string('marital_status', 50)->nullable();
            $table->string('nationality', 100)->nullable();
            $table->string('phone_number', 50)->nullable();
            $table->string('whatsapp_number', 50)->nullable();
            $table->text('current_address', 50)->nullable();
            $table->string('linkedin')->nullable();
            $table->string('instagram')->nullable();
            $table->text('self_pr')->nullable();
            $table->text('address')->nullable();

            $table->timestamps();

            $table->unique('user_id');
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
