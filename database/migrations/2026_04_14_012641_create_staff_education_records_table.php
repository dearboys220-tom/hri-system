<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_education_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_user_id')->constrained('users')->onDelete('cascade');
            $table->string('module_code');          // company_rules / pdp_basic 等
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->unsignedTinyInteger('quiz_score')->nullable();   // 0-100
            $table->unsignedTinyInteger('attempt_count')->default(0);
            $table->timestamps();

            $table->unique(['staff_user_id', 'module_code']);
            $table->index(['staff_user_id', 'is_completed']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_education_records');
    }
};