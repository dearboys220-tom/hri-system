<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monthly_accounting_reports', function (Blueprint $table) {
            $table->id();

            // ── 対象月
            $table->string('report_month');        // 例: "2026-04"
            $table->date('period_from');
            $table->date('period_to');

            // ── ステータス
            // draft / ai_organized / pending_approval / approved / sent / acknowledged
            $table->string('status')->default('draft');

            // ── 集計データ（invoices から自動集計）
            $table->bigInteger('total_revenue')->default(0);     // 売上合計（paid）
            $table->bigInteger('total_pending')->default(0);     // 未収合計（sent）
            $table->integer('paid_invoice_count')->default(0);
            $table->integer('pending_invoice_count')->default(0);
            $table->json('included_invoice_ids')->nullable();    // 対象請求書IDリスト

            // ── 経費・支出（手動入力）
            $table->json('expense_items')->nullable();           // [{name, amount, receipt_no}]
            $table->bigInteger('total_expenses')->default(0);

            // ── AI整理結果（H-1）
            $table->text('ai_summary')->nullable();              // AI月次サマリー
            $table->text('ai_anomaly_notes')->nullable();        // 異常値・注意点
            $table->text('ai_draft_cover_letter')->nullable();   // 外部会計会社向け送付文草案
            $table->text('ai_checklist')->nullable();            // 送付前チェックリスト
            $table->json('ai_risk_flags')->nullable();
            $table->timestamp('ai_generated_at')->nullable();

            // ── 承認・送付
            $table->foreignId('created_by_user_id')->constrained('users');
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->string('sent_to_email')->nullable();         // 外部会計会社メールアドレス
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('acknowledged_at')->nullable();    // 受領確認
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique('report_month'); // 同月の重複作成禁止
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_accounting_reports');
    }
};