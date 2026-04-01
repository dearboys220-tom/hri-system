<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('review_items', function (Blueprint $table) {
            // case_reviews との紐付け
            $table->unsignedBigInteger('case_review_id')->nullable()->after('certification_request_id');

            // 加点方式の新カラム追加（旧減点カラムはそのまま残して段階移行）
            $table->integer('max_score')->nullable()->after('weight');
            $table->integer('actual_score')->nullable()->after('max_score');

            // AIの評価詳細（ai_reasoningを置き換え・拡充）
            $table->text('score_reason')->nullable()->after('actual_score');
            $table->text('evidence_summary')->nullable()->after('score_reason');
            $table->string('verification_status', 30)->nullable()->after('evidence_summary');
            // VERIFIED / PARTIALLY_VERIFIED / UNVERIFIED / CONTRADICTED

            $table->string('ai_model', 50)->nullable()->after('verification_status');
        });
    }

    public function down(): void
    {
        Schema::table('review_items', function (Blueprint $table) {
            $table->dropColumn([
                'case_review_id',
                'max_score',
                'actual_score',
                'score_reason',
                'evidence_summary',
                'verification_status',
                'ai_model',
            ]);
        });
    }
};