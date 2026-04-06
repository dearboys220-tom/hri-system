<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * v2.5: ai_chat_logs テーブル新設
     *
     * 調査部・審査管理部がAIチャット機能を利用した際の会話履歴を記録するテーブル。
     *
     * ⚠️ 重要な注意事項:
     *   - rawプロンプトは原則保存しない。保存前マスキング必須。
     *   - 個人識別情報（NIK・電話番号等）が含まれていた場合は
     *     masked_content にマスク済み版を保存し、contains_pii_flag = true をセット。
     *   - 保存期間: 90日で自動削除（Laravelスケジューラで実行）
     *   - 自由入力よりテンプレート優先の運用を推奨。
     *
     * message_role:
     *   user      : スタッフの発言
     *   assistant : AIの返答
     */
    public function up(): void
    {
        Schema::create('ai_chat_logs', function (Blueprint $table) {
            $table->id();

            // v2.6: 関連案件番号
            $table->string('case_no')->nullable()
                  ->comment('関連案件番号');

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->comment('チャット利用者ID');

            $table->string('role_type')
                  ->comment('investigator_user / admin_user');

            $table->string('session_id')
                  ->comment('チャットセッション単位のUUID（同一セッションの会話をグルーピング）');

            $table->string('related_type')->nullable()
                  ->comment('関連モデル名（CertificationRequest 等）');

            $table->unsignedBigInteger('related_id')->nullable()
                  ->comment('関連案件ID');

            $table->string('message_role')
                  ->comment('user / assistant');

            $table->text('message_content')
                  ->comment('チャット内容（個人識別情報は除く・マスキング済み）');

            // PII検知フラグ
            $table->boolean('contains_pii_flag')->default(false)
                  ->comment('PII含有の疑いが検知されたか（trueの場合、masked_contentを参照）');

            $table->text('masked_content')->nullable()
                  ->comment('PIIをマスクした後のテキスト（contains_pii_flag = trueの場合のみ）');

            $table->string('blocked_reason')->nullable()
                  ->comment('送信ブロックされた場合の理由');

            $table->unsignedInteger('tokens_used')->nullable()
                  ->comment('このメッセージのトークン数');

            $table->string('model_name')->nullable()
                  ->comment('使用したAIモデル名');

            $table->timestamp('created_at')->useCurrent()
                  ->comment('発言日時');

            // インデックス
            $table->index('case_no');
            $table->index('user_id');
            $table->index('session_id');
            $table->index('created_at');
            // 90日自動削除のスケジューラ用インデックス
            $table->index('created_at', 'idx_acl_created_for_cleanup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_chat_logs');
    }
};
