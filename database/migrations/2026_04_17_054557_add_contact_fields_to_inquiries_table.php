<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            // 公開フォーム用（未ログインユーザー対応）
            $table->string('name', 100)->nullable()->after('user_type');
            $table->string('email', 255)->nullable()->after('name');
            $table->string('phone', 30)->nullable()->after('email');
            $table->string('category', 50)->nullable()->after('phone');
            $table->string('source', 50)->nullable()->default('contact_form')->after('closed_at');
        });
    }

    public function down(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            $table->dropColumn(['name', 'email', 'phone', 'category', 'source']);
        });
    }
};