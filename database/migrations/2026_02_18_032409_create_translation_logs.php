<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       Schema::create('translation_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('investigation_item_id')
                ->constrained('investigation_items')
                ->cascadeOnDelete();

            $table->string('source_language', 10);

            $table->string('target_language', 10);

            $table->text('original_text');

            $table->text('translated_text');

            $table->string('translation_engine')->nullable();

            $table->string('status');

            $table->timestamp('translated_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translation_logs');
    }
};
