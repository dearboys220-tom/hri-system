<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // users.role_type は string 型なので追加作業は不要
        // ただしロール登録ロジック（StaffAuthController 等）側での許可リストに追加が必要
        // このMigrationはドキュメント目的・将来のseeder用として記録する
        // 実際のDBカラム変更は不要（role_type は string 型のため自由値）
    }

    public function down(): void
    {
        // nothing
    }
};