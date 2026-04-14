<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_number_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('number_type', 20);
            $table->string('period_key', 10);
            $table->unsignedBigInteger('last_sequence')->default(0);
            $table->string('dept_code', 30)->nullable();
            $table->timestamps();

            // インデックス名を短く指定（MySQL 64文字制限対策）
            $table->unique(['number_type', 'period_key', 'dept_code'], 'doc_num_seq_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_number_sequences');
    }
};