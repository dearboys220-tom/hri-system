<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estimates', function (Blueprint $table) {
            $table->id();

            // ── 番号（Section 28.1: HRI-EST-SALES-YYYY-NNNN）
            $table->string('estimate_no')->unique()->nullable();

            // ── 基本情報
            $table->string('title');
            $table->string('client_name');
            $table->string('client_email')->nullable();
            $table->string('service_type'); // G-1 サービス種別
            $table->text('scope_included')->nullable();   // 対象範囲
            $table->text('scope_excluded')->nullable();   // 対象外範囲
            $table->text('special_notes')->nullable();

            // ── 金額（IDR）
            $table->bigInteger('subtotal')->default(0);
            $table->boolean('discount_exists')->default(false);
            $table->bigInteger('discount_amount')->default(0);
            $table->text('discount_reason')->nullable(); // 値引き理由必須（Section 30.4）
            $table->bigInteger('final_amount')->default(0);
            $table->string('tax_note')->nullable();
            $table->text('payment_terms')->nullable();   // 支払条件

            // ── 有効期限（Section 29.4）
            $table->integer('validity_days')->default(14);
            $table->date('valid_until')->nullable();

            // ── フラグ
            $table->boolean('contract_required')->default(false);
            $table->boolean('nda_required')->default(false);

            // ── ステータス（Section 29.4）
            // draft / pending_approval / approved / sent / accepted / revised / expired
            $table->string('status')->default('draft');

            // ── AI生成結果（G-1）
            $table->text('ai_estimate_body')->nullable();       // AI生成の見積本文
            $table->text('ai_cover_email_draft')->nullable();   // AI生成のメール草案
            $table->text('ai_approval_note')->nullable();       // AI承認メモ
            $table->json('ai_risk_flags')->nullable();
            $table->json('ai_missing_items')->nullable();
            $table->timestamp('ai_generated_at')->nullable();

            // ── 承認フロー（Section 26 承認マトリクス）
            $table->foreignId('created_by_user_id')->constrained('users');
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            // ── 送付・受注
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('expired_at')->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index('client_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estimates');
    }
};