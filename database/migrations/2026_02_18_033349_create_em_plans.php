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
        Schema::create('em_plans', function (Blueprint $table) {
            $table->id();

            $table->string('plan_name');

            $table->enum('plan_type', ['monthly', 'lifetime', 'free']);

            $table->integer('max_employees')->nullable();

            $table->unsignedBigInteger('price')->default(0);

            $table->json('features')->nullable();

            $table->boolean('is_active')->default(true)->index();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('em_plans');
    }
};
