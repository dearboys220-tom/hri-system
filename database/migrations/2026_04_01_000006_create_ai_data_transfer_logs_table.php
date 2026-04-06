<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * v2.5: ai_data_transfer_logs テーブル新設
     *
     * Anthropic APIへのデータ送信記録。
     * PDP法の「越境移転制限」要件に対応。
     * 「いつ・何のデータを・どのAIモデルに送信したか」を全件記録する。
     *
     * transfer_purpose 一覧:
     *   scoring            : HRI認証スコアリング（HriAiScoringService）
     *   priority_analysis  : 調査優先度分析（HriPriorityAnalysisService）
     *   chat_support       : AIチャット支援（調査部・審査管理部）
     *   em_action          : EMモジュールのAI指示処理
     *
     * ⚠️ 送信前に必ず以下を確認すること:
     *   - is_anonymized = true（匿名化・仮名化済み）
     *   - 氏名・NIK・電話番号・メールアドレス・住所は含まない
     *   - consent_record_id に有効な同意レコードが存在する
     */
    public function up(): void
    {
        Schema::create('ai_data_transfer_logs', function (Blueprint $table) {
            $table->id();

            // 関連案件（nullable: チャット等は案件紐付けなしの場合もある）
            $table->unsignedBigInteger('certification_request_id')->nullable()
                  ->comment('関連認証申請ID（nullable）');

            $table->string('transfer_purpose')
                  ->comment('scoring / priority_analysis / chat_support / em_action');

            $table->string('ai_model')
                  ->comment('送信先AIモデル名（例：claude-sonnet-4-6）');

            $table->string('ai_provider')->default('Anthropic')
                  ->comment('AIプロバイダー名');

            $table->json('data_categories_sent')
                  ->comment('送信したデータカテゴリ（個人識別情報は含まない）例: ["work_history","education"]');

            $table->boolean('is_anonymized')->default(false)
                  ->comment('匿名化・仮名化済みか（trueでなければ送信禁止）');

            $table->string('legal_basis')->default('consent')
                  ->comment('consent / legitimate_interest');

            $table->unsignedBigInteger('consent_record_id')->nullable()
                  ->comment('根拠となる同意レコードID（consent_records.id）');

            $table->timestamp('transferred_at')->useCurrent()
                  ->comment('送信日時');

            // インデックス
            $table->index('certification_request_id');
            $table->index('transfer_purpose');
            $table->index('transferred_at');

            // 外部キー
            $table->foreign('certification_request_id')
                  ->references('id')
                  ->on('certification_requests')
                  ->nullOnDelete();

            $table->foreign('consent_record_id')
                  ->references('id')
                  ->on('consent_records')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_data_transfer_logs');
    }
};
