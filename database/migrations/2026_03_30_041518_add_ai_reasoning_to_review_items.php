<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('review_items', function (Blueprint $table) {
            $table->text('ai_reasoning')->nullable()->after('notes');
            $table->boolean('is_ai_scored')->default(false)->after('ai_reasoning');
        });
    }

    public function down(): void
    {
        Schema::table('review_items', function (Blueprint $table) {
            $table->dropColumn(['ai_reasoning', 'is_ai_scored']);
        });
    }
};