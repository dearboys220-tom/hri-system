<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * v2.5: data_deletion_requests テーブル新設
     *
     * ユーザーからの個人データ削除申請を管理するテーブル。
     * PDP法の「本人の権利（削除権）」要件に対応。
     * スーパー管理者（オーナー）が承認・処理を行う。
     *
     * scope 一覧:
     *   all_data             : 全データ削除
     *   profile_only         : プロフィールのみ（認証履歴は残す）
     *   certification_data   : 認証関連データのみ
     *
     * status 一覧:
     *   pending              : 申請受付済み・未対応
     *   under_review         : スーパー管理者が確認中
     *   approved             : 削除承認・処理中
     *   completed            : 削除完了
     *   rejected             : 却下（法的保存義務がある場合等）
     */
    public function up(): void
    {
        Schema::create('data_deletion_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->comment('削除を申請したユーザーID');

            $table->text('request_reason')->nullable()
                  ->comment('削除理由（ユーザー入力）');

            $table->string('scope')->default('all_data')
                  ->comment('all_data / profile_only / certification_data');

            $table->string('status')->default('pending')
                  ->comment('pending / under_review / approved / completed / rejected');

            $table->text('reject_reason')->nullable()
                  ->comment('却下理由（法的保存義務がある場合等）');

            $table->unsignedBigInteger('processed_by')->nullable()
                  ->comment('対応したスーパー管理者のユーザーID');

            $table->timestamp('requested_at')->useCurrent()
                  ->comment('申請日時');

            $table->timestamp('completed_at')->nullable()
                  ->comment('削除完了日時');

            $table->timestamps();

            // インデックス
            $table->index('user_id');
            $table->index('status');
            $table->index('requested_at');

            // 外部キー（super_admin ユーザーへの参照）
            $table->foreign('processed_by')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_deletion_requests');
    }
};
