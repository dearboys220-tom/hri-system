<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * v2.5: ai_activity_logs テーブル新設
     *
     * HRIシステム内の全AI処理を記録するテーブル。
     * コスト管理・説明責任・監査・スーパー管理者ダッシュボードに使用する。
     *
     * log_type 一覧:
     *   scoring            : HRI認証スコアリング
     *   priority_analysis  : 調査優先度分析
     *   chat_support       : AIチャット支援
     *   em_action          : EMモジュールのAI指示処理
     *
     * status 一覧:
     *   success   : 正常完了
     *   failed    : エラー発生
     *   timeout   : タイムアウト
     *
     * 💡 コスト計算:
     *   tokens_total をもとにスーパー管理者画面でIDR換算して表示する。
     *   claude-sonnet-4-6 の場合、入力/出力トークン単価を設定ファイルで管理する。
     */
    public function up(): void
    {
        Schema::create('ai_activity_logs', function (Blueprint $table) {
            $table->id();

            $table->string('log_type')
                  ->comment('scoring / priority_analysis / chat_support / em_action');

            $table->string('related_type')->nullable()
                  ->comment('関連モデル名（CertificationRequest / EmTask 等）');

            $table->unsignedBigInteger('related_id')->nullable()
                  ->comment('関連レコードID');

            $table->unsignedBigInteger('triggered_by_user_id')->nullable()
                  ->comment('AIを起動したユーザーID（自動起動時はnull）');

            $table->string('triggered_by_role')->nullable()
                  ->comment('起動者のロール');

            $table->string('model_name')
                  ->comment('使用したAIモデル名（例：claude-sonnet-4-6）');

            $table->string('prompt_version')->nullable()
                  ->comment('使用したプロンプトバージョン');

            $table->text('input_summary')->nullable()
                  ->comment('送信データの概要（個人識別情報は絶対に含めない）');

            $table->text('output_summary')->nullable()
                  ->comment('AIレスポンスの概要');

            $table->string('final_decision')->nullable()
                  ->comment('AI判定結果（scoring時: APPROVE / REJECT 等）');

            // トークン数・コスト管理
            $table->unsignedInteger('tokens_input')->nullable()
                  ->comment('入力トークン数');

            $table->unsignedInteger('tokens_output')->nullable()
                  ->comment('出力トークン数');

            $table->unsignedInteger('tokens_total')->nullable()
                  ->comment('合計トークン数（コスト計算用）');

            $table->decimal('estimated_cost_idr', 12, 2)->nullable()
                  ->comment('推定コスト（IDR換算）');

            $table->unsignedInteger('latency_ms')->nullable()
                  ->comment('応答時間（ミリ秒）');

            $table->string('status')->default('success')
                  ->comment('success / failed / timeout');

            $table->text('error_message')->nullable()
                  ->comment('エラーメッセージ（status = failed の場合）');

            $table->timestamp('created_at')->useCurrent()
                  ->comment('実行日時');

            // インデックス
            $table->index('log_type');
            $table->index(['related_type', 'related_id'], 'idx_aal_related');
            $table->index('triggered_by_user_id');
            $table->index('status');
            $table->index('created_at');

            // 外部キー
            $table->foreign('triggered_by_user_id')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_activity_logs');
    }
};
