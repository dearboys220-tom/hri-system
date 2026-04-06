<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * v2.6: case_reviews テーブルへの追加カラム（AI/人間/確定の3分離）
     *
     * 追加内容:
     *   - input_hash             : 送信入力データのハッシュ（再現性・監査用）
     *   - confidence_level       : AIの自己評価信頼度（HIGH / MEDIUM / LOW）
     *   - ai_proposed_decision   : AIが提案した判定
     *   - human_override_decision: 人間が上書きした判定（null = AI提案を採用）
     *   - human_override_reason  : 人間が上書きした理由
     *   - effective_decision     : 最終確定値（human_override ?? ai_proposed）
     *   - approved_by_user_id    : 確定した審査管理部スタッフID
     *   - approved_at            : 確定日時
     *
     * 3分離の運用ルール:
     *   AI審査完了時   → ai_proposed_decision に値セット
     *                    effective_decision = ai_proposed_decision
     *   人間が同意     → human_override_decision = null のまま確定
     *                    (approved_by_user_id / approved_at をセット)
     *   人間が上書き   → human_override_decision に値セット
     *                    effective_decision を上書き値に更新
     *
     * decision 値:
     *   APPROVE / CONDITIONAL_APPROVE / REJECT /
     *   ESCALATE_TO_HUMAN / RETURN_TO_INVESTIGATION
     */
    public function up(): void
    {
        Schema::table('case_reviews', function (Blueprint $table) {

            // ① 入力ハッシュ・信頼度
            $table->string('input_hash')->nullable()
                  ->after('model_name')
                  ->comment('送信した入力データのSHA-256ハッシュ（再現性・監査用）');

            $table->string('confidence_level')->nullable()
                  ->after('input_hash')
                  ->comment('AIの自己評価信頼度: HIGH / MEDIUM / LOW');

            // ② 3分離カラム
            $table->string('ai_proposed_decision')->nullable()
                  ->after('compliance_return_json')
                  ->comment('AIが提案した判定: APPROVE / CONDITIONAL_APPROVE / REJECT / ESCALATE_TO_HUMAN / RETURN_TO_INVESTIGATION');

            $table->string('human_override_decision')->nullable()
                  ->after('ai_proposed_decision')
                  ->comment('人間が上書きした判定（null = AI提案を採用）');

            $table->text('human_override_reason')->nullable()
                  ->after('human_override_decision')
                  ->comment('人間が上書きした理由（audit証跡として必須）');

            $table->string('effective_decision')->nullable()
                  ->after('human_override_reason')
                  ->comment('最終確定値（human_override_decision ?? ai_proposed_decision）');

            // ③ 承認者情報
            $table->unsignedBigInteger('approved_by_user_id')->nullable()
                  ->after('effective_decision')
                  ->comment('確定した審査管理部スタッフID');

            $table->timestamp('approved_at')->nullable()
                  ->after('approved_by_user_id')
                  ->comment('確定日時');

            // ④ case_no（共通軸）
            $table->string('case_no')->nullable()
                  ->after('id')
                  ->comment('案件番号（certification_requests.case_no と一致）');

            // インデックス
            $table->index('case_no');
            $table->index('ai_proposed_decision');
            $table->index('effective_decision');

            // 外部キー
            $table->foreign('approved_by_user_id')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('case_reviews', function (Blueprint $table) {
            $table->dropForeign(['approved_by_user_id']);
            $table->dropIndex(['case_no']);
            $table->dropIndex(['ai_proposed_decision']);
            $table->dropIndex(['effective_decision']);
            $table->dropColumn([
                'case_no',
                'input_hash',
                'confidence_level',
                'ai_proposed_decision',
                'human_override_decision',
                'human_override_reason',
                'effective_decision',
                'approved_by_user_id',
                'approved_at',
            ]);
        });
    }
};
