<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * v2.5: applicant_profiles テーブルへの追加カラム
     * - data_retention_expires_at : PDP法対応・データ保存期限
     */
    public function up(): void
    {
        Schema::table('applicant_profiles', function (Blueprint $table) {
            $table->date('data_retention_expires_at')->nullable()
                  ->after('free_certification_expires_at')
                  ->comment('PDP法対応：データ保存期限。期限到来後はスーパー管理者が削除対応。');
        });
    }

    public function down(): void
    {
        Schema::table('applicant_profiles', function (Blueprint $table) {
            $table->dropColumn('data_retention_expires_at');
        });
    }
};
