<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_task_bridge_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('internal_chat_log_id');
            $table->foreignId('issued_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->text('original_message_ja');
            $table->text('interpreted_instruction')->nullable();
            $table->string('target_division', 30)->nullable();
            $table->string('priority_level', 10)->default('NORMAL');
            $table->boolean('task_order_created')->default(false);
            $table->unsignedBigInteger('task_order_id')->nullable();
            $table->string('bridge_status', 20)->default('PENDING');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_task_bridge_logs');
    }
};