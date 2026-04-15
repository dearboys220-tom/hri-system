<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inquiries', function (Blueprint $table) {
            $table->id();

            // ── 問い合わせ番号（Section 28.1: HRI-QRY-YYYYMMDD-NNNN）
            $table->string('inquiry_no')->unique()->nullable();

            // ── 送信者情報
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('user_type'); // applicant / company

            // ── 問い合わせ本文
            $table->string('subject');
            $table->text('body');

            // ── ステータス（Section 29.3）
            // received / classified / answered / escalated / closed
            $table->string('status')->default('received');

            // ── SLA管理（Section 30.3）
            $table->timestamp('sla_deadline')->nullable(); // 一般会員2営業日 / 企業1営業日 / 苦情当日
            $table->boolean('sla_breached')->default(false);

            // ── AI分類結果（D-1/D-3 JSON出力を保存）
            $table->string('ai_category')->nullable();       // account_registration / payment_issue 等
            $table->string('ai_priority')->nullable();       // low / normal / high / urgent
            $table->boolean('ai_can_answer_immediately')->default(false);
            $table->boolean('ai_answer_prohibited')->default(false);
            $table->boolean('ai_identity_check_required')->default(false);
            $table->boolean('ai_requires_supervisor_review')->default(false);
            $table->boolean('ai_requires_legal_review')->default(false);
            $table->boolean('ai_requires_pdp_review')->default(false);
            $table->boolean('ai_should_escalate')->default(false);
            $table->text('ai_reason_summary')->nullable();
            $table->text('ai_recommended_next_action')->nullable();
            $table->text('ai_draft_reply_direction')->nullable();
            $table->json('ai_risk_flags')->nullable();
            $table->timestamp('ai_classified_at')->nullable();

            // ── 人間回答
            $table->text('human_reply')->nullable();
            $table->foreignId('replied_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('replied_at')->nullable();

            // ── クローズ
            $table->timestamp('closed_at')->nullable();

            $table->timestamps();

            // インデックス
            $table->index('user_id');
            $table->index('status');
            $table->index('ai_priority');
            $table->index('sla_deadline');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inquiries');
    }
};