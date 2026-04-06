<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * v2.6: certification_requests テーブルへの追加カラム
     *
     * 追加内容:
     *   - case_no                : 案件番号（全テーブル共通軸）
     *   - current_status         : 内部ステータス（スタッフ・システムが参照）
     *   - external_status        : 外部表示ステータス（個人会員・企業向け）
     *   - survey_status          : 後方互換用（current_status と同期）@deprecated
     *   - latest_return_id       : 最新の差し戻しID
     *   - internal_return_required : 差し戻し中フラグ
     *   - deliverable_vr_status  : VR状態（case_deliverablesと同期）
     *   - deliverable_ir_status  : IR状態（case_deliverablesと同期）
     *   - deliverable_rn_status  : RN状態（case_deliverablesと同期）
     *   - approved_by_user_id    : 確定した審査管理部スタッフID
     *   - approved_at            : 最終承認日時
     *
     * current_status 値:
     *   draft / under_investigation / ai_review_pending /
     *   returned_internal / human_review_required /
     *   conditionally_verified / verified / rejected
     *
     * external_status 値:
     *   under_review / additional_check_in_progress /
     *   conditionally_verified / verified / rejected
     *
     * ⚠️ current_status = 'returned_internal' のとき、
     *    外部には external_status = 'additional_check_in_progress' を表示。
     *    差し戻し理由は絶対に外部表示しないこと。
     */
    public function up(): void
    {
        Schema::table('certification_requests', function (Blueprint $table) {

            // ① case_no（全テーブル共通軸）
            // UNIQUE は後から追加。既存データがある場合は先にnullableで追加。
            $table->string('case_no')->nullable()->unique()
                  ->after('id')
                  ->comment('案件番号（例：CR-20260001）。全テーブルの共通軸。');

            // ② ステータス二重化
            $table->string('current_status')->nullable()
                  ->after('region_id')
                  ->comment('内部ステータス: draft / under_investigation / ai_review_pending / returned_internal / human_review_required / conditionally_verified / verified / rejected');

            $table->string('external_status')->nullable()
                  ->after('current_status')
                  ->comment('外部表示ステータス: under_review / additional_check_in_progress / conditionally_verified / verified / rejected');

            // ③ 差し戻し管理
            $table->unsignedBigInteger('latest_return_id')->nullable()
                  ->after('latest_case_review_id')
                  ->comment('最新の差し戻しID（case_returns.id）');

            $table->boolean('internal_return_required')->default(false)
                  ->after('latest_return_id')
                  ->comment('差し戻し中フラグ（true = 現在差し戻し中）');

            // ④ 結果物ステータス（case_deliverablesと同期）
            $table->string('deliverable_vr_status', 20)->default('NOT_READY')
                  ->after('internal_return_required')
                  ->comment('VR状態: NOT_READY / PENDING / ISSUED / VOID');

            $table->string('deliverable_ir_status', 20)->default('NOT_READY')
                  ->after('deliverable_vr_status')
                  ->comment('IR状態: NOT_READY / PENDING / ISSUED / VOID');

            $table->string('deliverable_rn_status', 20)->default('NOT_REQUIRED')
                  ->after('deliverable_ir_status')
                  ->comment('RN状態: NOT_REQUIRED / ISSUED / VOID');

            // ⑤ 承認者情報
            $table->unsignedBigInteger('approved_by_user_id')->nullable()
                  ->after('admin_notes')
                  ->comment('最終承認した審査管理部スタッフID');

            $table->timestamp('approved_at')->nullable()
                  ->after('approved_by_user_id')
                  ->comment('最終承認日時');

            // インデックス
            $table->index('current_status');
            $table->index('external_status');
            $table->index('internal_return_required');

            // 外部キー
            $table->foreign('approved_by_user_id')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('certification_requests', function (Blueprint $table) {
            $table->dropForeign(['approved_by_user_id']);
            $table->dropIndex(['current_status']);
            $table->dropIndex(['external_status']);
            $table->dropIndex(['internal_return_required']);
            $table->dropUnique(['case_no']);
            $table->dropColumn([
                'case_no',
                'current_status',
                'external_status',
                'latest_return_id',
                'internal_return_required',
                'deliverable_vr_status',
                'deliverable_ir_status',
                'deliverable_rn_status',
                'approved_by_user_id',
                'approved_at',
            ]);
        });
    }
};
