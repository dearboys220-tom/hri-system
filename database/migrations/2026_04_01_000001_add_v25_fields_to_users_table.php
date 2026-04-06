<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * v2.5: users テーブルへの追加カラム
     * - status         : アカウント状態管理（active / suspended / deleted）
     * - last_login_at  : 最終ログイン日時（監査・スーパー管理者画面用）
     * - last_login_ip  : 最終ログインIP（監査用）
     *
     * ※ role_type への 'super_admin' 追加はコードレベルで対応（enum変更不要）
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // アカウントステータス（active がデフォルト）
            $table->string('status')->default('active')->after('role_type')
                  ->comment('active / suspended / deleted');

            // 最終ログイン情報（監査・スーパー管理者画面用）
            $table->timestamp('last_login_at')->nullable()->after('email_verified_at')
                  ->comment('最終ログイン日時');
            $table->string('last_login_ip')->nullable()->after('last_login_at')
                  ->comment('最終ログインIPアドレス');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['status', 'last_login_at', 'last_login_ip']);
        });
    }
};
