<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * v2.5: personal_data_access_logs テーブル新設
     *
     * 誰が誰の個人データにいつアクセスしたかを記録する。
     * PDP法の「アクセス制御・説明責任」要件に対応。
     * スーパー管理者のアクセスも必ず記録する。
     *
     * action 一覧:
     *   view             : 画面表示
     *   download         : ファイルダウンロード
     *   export           : データエクスポート
     *   ai_send          : AIへのデータ送信
     *
     * access_reason 一覧:
     *   investigation    : 調査目的
     *   review           : 審査目的
     *   company_purchase : 企業によるスコア詳細購入
     *   admin_check      : 管理者確認
     *   super_admin      : スーパー管理者アクセス
     */
    public function up(): void
    {
        Schema::create('personal_data_access_logs', function (Blueprint $table) {
            $table->id();

            // v2.6: 関連案件番号
            $table->string('case_no')->nullable()
                  ->comment('関連案件番号');

            $table->foreignId('accessor_user_id')
                  ->constrained('users')
                  ->comment('アクセスしたユーザーID');

            $table->string('accessor_role')
                  ->comment('アクセス時のロール（investigator_user / admin_user / company / super_admin）');

            $table->unsignedBigInteger('target_user_id')->nullable()
                  ->comment('アクセス対象のユーザーID');

            $table->string('target_member_id')->nullable()
                  ->comment('アクセス対象の会員ID（HRI-XXXXX）');

            // v2.6: アクセス対象テーブル・レコード
            $table->string('target_table')->nullable()
                  ->comment('アクセス対象のテーブル名（applicant_profiles 等）');

            $table->unsignedBigInteger('target_record_id')->nullable()
                  ->comment('アクセス対象のレコードID');

            $table->string('data_type')
                  ->comment('ktp / nik / work_history / education / full_profile / investigation_items 等');

            // v2.6: フィールドレベルのアクセス記録
            $table->json('fields_accessed_json')->nullable()
                  ->comment('アクセスしたフィールド名リスト（高感度項目のフィールドレベル記録）');

            $table->string('action')
                  ->comment('view / download / export / ai_send');

            $table->string('access_reason')
                  ->comment('investigation / review / company_purchase / admin_check / super_admin');

            $table->unsignedBigInteger('related_id')->nullable()
                  ->comment('関連する certification_request_id 等');

            $table->string('ip_address')->nullable();

            $table->timestamp('accessed_at')->useCurrent()
                  ->comment('アクセス日時');

            // インデックス
            $table->index('case_no');
            $table->index('accessor_user_id');
            $table->index('target_user_id');
            $table->index('accessed_at');
            $table->index(['target_user_id', 'accessed_at'],
                          'idx_pdal_target_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personal_data_access_logs');
    }
};
