<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applicant_profiles', function (Blueprint $table) {
            $table->string('full_name')->nullable()->change();
            $table->string('gender')->nullable()->change();
            $table->date('birth_date')->nullable()->change();
            $table->string('nationality')->nullable()->change();
            $table->string('marital_status')->nullable()->change();
            $table->string('phone_number')->nullable()->change();
            $table->string('whatsapp_number')->nullable()->change();
            $table->text('current_address')->nullable()->change();
        });
    }

    public function down(): void
    {
        //
    }
};