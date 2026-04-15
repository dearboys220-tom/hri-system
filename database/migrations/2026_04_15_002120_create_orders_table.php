<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // ── 番号（Section 28.1: HRI-ORD-SALES-YYYY-NNNN）
            $table->string('order_no')->unique()->nullable();

            // ── 見積との紐付け
            $table->foreignId('estimate_id')->constrained('estimates')->cascadeOnDelete();

            // ── 基本情報（見積から引き継ぎ）
            $table->string('client_name');
            $table->string('client_email')->nullable();
            $table->string('service_type');
            $table->bigInteger('final_amount');
            $table->text('payment_terms')->nullable();
            $table->text('notes')->nullable();

            // ── ステータス
            // confirmed / in_progress / completed / cancelled
            $table->string('status')->default('confirmed');

            // ── 承認・担当者
            $table->foreignId('created_by_user_id')->constrained('users');
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->index('estimate_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};