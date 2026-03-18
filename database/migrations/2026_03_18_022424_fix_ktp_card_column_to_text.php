<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applicant_profiles', function (Blueprint $table) {
            $table->text('ktp_card')->nullable()->change();
            $table->text('ktp_address')->nullable()->change();
        });
    }

    public function down(): void {}
};