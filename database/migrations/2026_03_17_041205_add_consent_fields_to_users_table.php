<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('agreed_terms_at')->nullable()->after('email_verified_at');
            $table->timestamp('agreed_investigation_at')->nullable()->after('agreed_terms_at');
            $table->string('terms_version')->default('2025-01')->after('agreed_investigation_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['agreed_terms_at', 'agreed_investigation_at', 'terms_version']);
        });
    }
};
