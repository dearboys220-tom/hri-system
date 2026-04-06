<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * v2.5: consent_records テーブル新設
     *
     * ユーザーの個人情報利用同意を管理するテーブル。
     * PDP法（UU PDP No.27/2022）の「目的の明示・同意取得」要件に対応。
     *
     * consent_type 一覧:
     *   - ACCOUNT_TERMS           : 会員登録・利用規約への同意（必須）
     *   - ACCOUNT_PRIVACY         : プライバシーポリシーへの同意（必須）
     *   - ai_processing           : AIによる個人情報の自動処理への同意（認証申請時必須）
     *   - data_transfer_abroad    : Anthropic API（米国）へのデータ送信への同意（認証申請時必須）
     *   - VERIFIED_RESUME_THIRD_PARTY_VIEW : VRの第三者（企業）閲覧への同意（v2.6追加）
     *   - COOKIE_ANALYTICS        : 分析用Cookieへの同意（任意）
     *   - COOKIE_MARKETING        : マーケティング用Cookieへの同意（任意）
     *   - marketing               : マーケティング目的の利用への同意（任意）
     *
     * ⚠️ ai_processing と data_transfer_abroad の同意がない場合、
     *    certification_requests を作成してはならない。
     */
    public function up(): void
    {
        Schema::create('consent_records', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete()
                  ->comment('同意したユーザーID');

            // v2.6: 関連案件番号（認証申請時の同意の場合）
            $table->string('case_no')->nullable()
                  ->comment('関連案件番号（認証申請時の同意の場合）');

            $table->string('consent_type')
                  ->comment('ACCOUNT_TERMS / ACCOUNT_PRIVACY / ai_processing / data_transfer_abroad / VERIFIED_RESUME_THIRD_PARTY_VIEW / COOKIE_ANALYTICS / COOKIE_MARKETING / marketing');

            $table->string('consent_version')->default('v1.0')
                  ->comment('同意書バージョン（文言改定時に更新）');

            // v2.6: 同意文書内容のハッシュ（文言改定前後の証明用）
            $table->string('consent_hash')->nullable()
                  ->comment('同意文書内容のSHA-256ハッシュ（文言改定前後の証明用）');

            $table->boolean('consented')->default(true)
                  ->comment('true = 同意 / false = 拒否');

            $table->timestamp('consented_at')->nullable()
                  ->comment('同意日時');

            $table->timestamp('withdrawn_at')->nullable()
                  ->comment('同意撤回日時（null = 有効）');

            // v2.6: 同意取得チャネル
            $table->string('source_channel')->default('web')
                  ->comment('web / wa / phone / admin_input');

            // v2.6: 企業閲覧トークン（VERIFIED_RESUME_THIRD_PARTY_VIEW の場合）
            $table->string('token_id')->nullable()
                  ->comment('関連閲覧トークンID（VERIFIED_RESUME_THIRD_PARTY_VIEW の場合）');

            $table->timestamp('expires_at')->nullable()
                  ->comment('同意有効期限');

            $table->integer('remaining_views')->nullable()
                  ->comment('残閲覧可能回数（VERIFIED_RESUME_THIRD_PARTY_VIEW の場合）');

            $table->string('ip_address')->nullable()
                  ->comment('同意時のIPアドレス');

            $table->text('user_agent')->nullable()
                  ->comment('同意時のブラウザ情報');

            $table->timestamps();

            // インデックス
            $table->index('user_id');
            $table->index('consent_type');
            $table->index('case_no');
            $table->index(['user_id', 'consent_type', 'withdrawn_at'],
                          'idx_consent_user_type_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consent_records');
    }
};
