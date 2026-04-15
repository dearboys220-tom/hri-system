<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            // ── 番号（Section 28.1: HRI-INV-SALES-YYYY-NNNN）
            $table->string('invoice_no')->unique()->nullable();

            // ── 受注・見積との紐付け
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('estimate_id')->constrained('estimates');

            // ── 基本情報
            $table->string('client_name');
            $table->string('client_email')->nullable();
            $table->string('service_type');
            $table->bigInteger('subtotal');
            $table->bigInteger('discount_amount')->default(0);
            $table->bigInteger('final_amount');
            $table->string('tax_note')->nullable();
            $table->text('payment_terms')->nullable();
            $table->date('due_date')->nullable(); // 支払期限

            // ── ステータス
            // draft / pending_approval / approved / sent / paid / overdue / cancelled
            $table->string('status')->default('draft');

            // ── 支払確認
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_method')->nullable();
            $table->text('payment_notes')->nullable();

            // ── 承認・送付
            $table->foreignId('created_by_user_id')->constrained('users');
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('sent_at')->nullable();

            $table->timestamps();

            $table->index('order_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};