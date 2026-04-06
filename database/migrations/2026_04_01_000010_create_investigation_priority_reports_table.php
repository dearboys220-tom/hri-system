<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * v2.5: investigation_priority_reports テーブル新設
     *
     * 応募者が認証申請を送信した時点でAIが事前分析し、
     * 調査部の調査優先度を示すレポートを自動生成するテーブル。
     *
     * 生成タイミング:
     *   certification_requests 作成直後、Queueジョブで非同期実行。
     *   HriPriorityAnalysisService が担当。
     *
     * 調査工数削減の目安:
     *   従来: 全40〜50項目を均等確認
     *   導入後: 最優先10〜15項目に集中（約40〜50%の調査時間削減見込み）
     *
     * priority_high_json の構造例:
     * [
     *   {
     *     "category": "work",
     *     "company": "PT Maju Jaya",
     *     "period": "2021-2023",
     *     "reason": "退職理由の記載なし。在籍期間が最長のため優先確認を推奨。",
     *     "priority": "HIGH"
     *   }
     * ]
     *
     * conduct_contacts_json の構造例:
     * [
     *   {
     *     "company": "PT Maju Jaya",
     *     "supervisor_name": "Budi Santoso",
     *     "supervisor_contact": "+62-812-xxxx-xxxx",
     *     "contact_available": true
     *   }
     * ]
     */
    public function up(): void
    {
        Schema::create('investigation_priority_reports', function (Blueprint $table) {
            $table->id();

            // v2.6: 案件番号（case_no軸）
            $table->string('case_no')
                  ->comment('案件番号（例：CR-20260001）');

            $table->foreignId('certification_request_id')
                  ->constrained('certification_requests')
                  ->cascadeOnDelete()
                  ->comment('関連認証申請ID');

            $table->string('ai_model')
                  ->comment('使用したAIモデル名（例：claude-sonnet-4-6）');

            $table->string('prompt_version')->nullable()
                  ->comment('使用したプロンプトバージョン');

            // 優先度別リスト
            $table->json('priority_high_json')->nullable()
                  ->comment('🔴 最優先確認項目リスト（必ず確認）');

            $table->json('priority_medium_json')->nullable()
                  ->comment('🟡 確認推奨項目リスト（できれば確認）');

            $table->json('priority_low_json')->nullable()
                  ->comment('🟢 省略可能項目リスト（低リスク・省略可）');

            // conductカテゴリ専用
            $table->json('conduct_contacts_json')->nullable()
                  ->comment('conductカテゴリの連絡先まとめ（会社・上司名・連絡先・連絡可否）');

            $table->json('risk_flags_json')->nullable()
                  ->comment('事前に検知されたリスクフラグリスト');

            $table->text('ai_analysis_summary')->nullable()
                  ->comment('AI総評テキスト（調査部向け・日本語 or インドネシア語）');

            $table->timestamp('generated_at')->useCurrent()
                  ->comment('レポート生成日時');

            $table->timestamps();

            // インデックス
            $table->index('case_no');
            $table->index('certification_request_id');
            $table->index('generated_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investigation_priority_reports');
    }
};
