<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('education_history', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('id');
        });

        Schema::table('work_history', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('id');
        });

        Schema::table('certifications', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('education_history', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });

        Schema::table('work_history', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });

        Schema::table('certifications', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
    }
};