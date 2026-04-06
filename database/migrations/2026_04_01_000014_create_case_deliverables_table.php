<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * v2.6: case_deliverables テーブル新設
     *
     * VR（Verified Resume）/ IR（Investigation Report）/ RN（Return Notice）を
     * 独立テーブルで一元管理する。
     *
     * ★ 最重要ルール（サービス層で必ず強制すること）:
     *   RN が ISSUED かつ is_active = true の間、
     *   VR / IR は PENDING のまま保留。
     *   RN が VOID になって初めて VR / IR を ISSUED に移行できる。
     *
     * deliverable_type 別 visibility_scope:
     *   VR → APPLICANT_VIEW  : 個人会員向け認証済み履歴書
     *   IR → COMPANY_VIEW    : 企業向け調査報告書
     *   RN → INTERNAL_ONLY   : 内部差し戻し通知（外部非公開）
     *
     * deliverable_status 遷移:
     *   【通常フロー】
     *     NOT_READY → PENDING（AI審査完了時）→ ISSUED（審査管理部承認時）
     *   【RN発生フロー】
     *     VR/IR: PENDING のまま保留
     *     RN:    ISSUED（差し戻し発生時）
     *     → 再調査・再審査完了後 RN: VOID → VR/IR: ISSUED
     *   【失効】
     *     ISSUED → VOID（有効期限切れ・再申請時）
     */
    public function up(): void
    {
        Schema::create('case_deliverables', function (Blueprint $table) {
            $table->id();

            $table->string('case_no')
                  ->comment('案件番号（certification_requests.case_no と一致）');

            $table->foreignId('certification_request_id')
                  ->constrained('certification_requests')
                  ->cascadeOnDelete()
                  ->comment('関連認証申請ID');

            $table->unsignedBigInteger('case_review_id')->nullable()
                  ->comment('紐付くAI審査ID（case_reviews.id）');

            $table->string('deliverable_type', 10)
                  ->comment('VR / IR / RN');

            $table->string('serial_no')->nullable()
                  ->comment('正式採番（発行時に採番）例: VR-2026-00001');

            $table->unsignedInteger('revision_no')->default(1)
                  ->comment('改訂番号（再発行のたびにインクリメント）');

            $table->string('visibility_scope')
                  ->comment('APPLICANT_VIEW / COMPANY_VIEW / INTERNAL_ONLY');

            $table->string('deliverable_status', 20)->default('NOT_READY')
                  ->comment('NOT_READY / PENDING / ISSUED / VOID');

            $table->timestamp('generated_at')->nullable()
                  ->comment('発行日時（ISSUED になった時刻）');

            $table->timestamp('expires_at')->nullable()
                  ->comment('有効期限（VR: 承認日 + 3ヶ月）');

            $table->string('file_path')->nullable()
                  ->comment('出力ファイルパス（PDF等）');

            $table->json('json_payload')->nullable()
                  ->comment('結果物データ本体（JSON形式）');

            $table->boolean('is_active')->default(true)
                  ->comment('現行有効フラグ（false = 無効化済み）');

            $table->timestamps();

            // インデックス
            $table->index('case_no');
            $table->index('certification_request_id');
            $table->index('deliverable_type');
            $table->index('deliverable_status');
            $table->index('is_active');
            $table->index(
                ['certification_request_id', 'deliverable_type', 'is_active'],
                'idx_cd_request_type_active'
            );

            // 外部キー
            $table->foreign('case_review_id')
                  ->references('id')
                  ->on('case_reviews')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_deliverables');
    }
};
