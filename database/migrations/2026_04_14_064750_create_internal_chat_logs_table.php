<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('internal_chat_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role_type', 30);
            $table->string('session_id', 100);
            $table->string('message_role', 10)->comment('user | assistant');
            $table->text('message_content');
            $table->text('message_content_ja')->nullable();
            $table->text('message_content_ko')->nullable();
            $table->text('message_content_id')->nullable();
            $table->string('source_language', 5)->default('ja');
            $table->boolean('is_task_instruction')->default(false);
            $table->unsignedBigInteger('task_order_id')->nullable();
            $table->integer('tokens_used')->nullable();
            $table->string('model_name', 50)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'session_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internal_chat_logs');
    }
};