<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * v2.6: enterprise_views テーブル新設
     *
     * 企業会員が IR（Investigation Report）を閲覧した際のログテーブル。
     * トークン管理・閲覧回数制限・有効期限を管理する。
     *
     * 閲覧制限ルール:
     *   - Verified Resume は既定90日・最大5回閲覧制限
     *   - consent_records（VERIFIED_RESUME_THIRD_PARTY_VIEW）と紐付けて制御
     *   - is_expired_view = true の場合は閲覧試行のみ記録し、コンテンツは返さない
     */
    public function up(): void
    {
        Schema::create('enterprise_views', function (Blueprint $table) {
            $table->id();

            $table->string('case_no')
                  ->comment('案件番号');

            $table->foreignId('company_user_id')
                  ->constrained('users')
                  ->comment('閲覧した企業ユーザーID');

            $table->foreignId('deliverable_id')
                  ->constrained('case_deliverables')
                  ->comment('閲覧した結果物ID（case_deliverables.id）');

            $table->string('token_id')->nullable()
                  ->comment('閲覧トークン（時限URL発行時）');

            $table->timestamp('viewed_at')->useCurrent()
                  ->comment('閲覧日時');

            $table->string('ip_address')->nullable();

            $table->text('user_agent')->nullable();

            $table->integer('remaining_views_after')->nullable()
                  ->comment('この閲覧後の残閲覧可能回数');

            $table->boolean('is_expired_view')->default(false)
                  ->comment('有効期限切れ後の閲覧試行か（true = ブロック済み）');

            $table->timestamps();

            // インデックス
            $table->index('case_no');
            $table->index('company_user_id');
            $table->index('deliverable_id');
            $table->index('viewed_at');
            $table->index(
                ['deliverable_id', 'company_user_id'],
                'idx_ev_deliverable_company'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enterprise_views');
    }
};
