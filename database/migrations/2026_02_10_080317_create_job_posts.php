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
        Schema::create('job_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('title');
            $table->string('category', 100)->nullable();
            $table->string('employment_type', 50)->nullable();
            $table->string('education_requirement', 100)->nullable();

            $table->integer('salary_min')->nullable();
            $table->integer('salary_max')->nullable();

            $table->string('location')->nullable();

            $table->json('required_skills')->nullable();
            $table->unsignedInteger('views')->default(0);

            $table->string('status', 50)->default('draft');

            $table->timestamps();

            $table->index(['company_id', 'status']);
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
