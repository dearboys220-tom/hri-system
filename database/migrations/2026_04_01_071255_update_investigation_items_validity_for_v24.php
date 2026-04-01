<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL の enum 変更は ALTER TABLE で直接実行
        DB::statement("ALTER TABLE investigation_items MODIFY COLUMN validity ENUM('VALID', 'INVALID', 'UNVERIFIED') NOT NULL DEFAULT 'UNVERIFIED'");
    }

    public function down(): void
    {
        // UNVERIFIEDを含むレコードをINVALIDに戻してからenum縮小
        DB::statement("UPDATE investigation_items SET validity = 'INVALID' WHERE validity = 'UNVERIFIED'");
        DB::statement("ALTER TABLE investigation_items MODIFY COLUMN validity ENUM('VALID', 'INVALID') NOT NULL");
    }
};