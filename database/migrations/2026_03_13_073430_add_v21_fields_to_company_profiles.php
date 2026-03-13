<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_profiles', function (Blueprint $table) {
            $table->string('nib')->nullable()->after('company_name');
            $table->string('pic_name')->nullable()->after('nib');
            $table->string('pic_position')->nullable()->after('pic_name');
            $table->string('pic_phone')->nullable()->after('pic_position');
            $table->string('akta_pendirian')->nullable()->after('pic_phone');
        });
    }

    public function down(): void
    {
        Schema::table('company_profiles', function (Blueprint $table) {
            $table->dropColumn(['nib', 'pic_name', 'pic_position', 'pic_phone', 'akta_pendirian']);
        });
    }
};