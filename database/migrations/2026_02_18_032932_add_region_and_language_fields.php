<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certification_requests', function (Blueprint $table) {
            $table->foreignId('region_id')
                ->nullable()
                ->constrained('regions')
                ->nullOnDelete();

            $table->index('region_id');
        });

        Schema::table('investigation_items', function (Blueprint $table) {
            $table->string('input_language', 10)->nullable();
            $table->text('notes_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('certification_requests', function (Blueprint $table) {
            $table->dropForeign(['region_id']);
            $table->dropColumn('region_id');
        });

        Schema::table('investigation_items', function (Blueprint $table) {
            $table->dropColumn(['input_language', 'notes_id']);
        });
    }
};
