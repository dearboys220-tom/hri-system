<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_availability', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->string('availability_status')->default('AVAILABLE');
            // AVAILABLE / BUSY / ON_LEAVE / SUSPENDED
            $table->unsignedInteger('active_task_count')->default(0);
            $table->unsignedInteger('max_concurrent_tasks')->default(3);
            $table->timestamp('last_reported_at')->nullable();
            $table->date('absence_until')->nullable();
            $table->string('department_code')->nullable();
            $table->text('skill_tags_json')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_availability');
    }
};