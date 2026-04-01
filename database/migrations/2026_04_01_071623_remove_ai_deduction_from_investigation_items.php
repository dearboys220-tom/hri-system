<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('investigation_items', function (Blueprint $table) {
            $table->dropColumn('ai_deduction');
        });
    }

    public function down(): void
    {
        Schema::table('investigation_items', function (Blueprint $table) {
            $table->integer('ai_deduction')->nullable()->after('notes');
        });
    }
};