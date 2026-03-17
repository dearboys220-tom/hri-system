<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('education_history', function (Blueprint $table) {
            $table->dropForeign(['certification_request_id']);
            $table->bigInteger('certification_request_id')->nullable()->change();
        });

        Schema::table('work_history', function (Blueprint $table) {
            $table->dropForeign(['certification_request_id']);
            $table->bigInteger('certification_request_id')->nullable()->change();
        });

        Schema::table('certifications', function (Blueprint $table) {
            $table->dropForeign(['certification_request_id']);
            $table->bigInteger('certification_request_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('education_history', function (Blueprint $table) {
            $table->bigInteger('certification_request_id')->nullable(false)->change();
            $table->foreign('certification_request_id')->references('id')->on('certification_requests');
        });

        Schema::table('work_history', function (Blueprint $table) {
            $table->bigInteger('certification_request_id')->nullable(false)->change();
            $table->foreign('certification_request_id')->references('id')->on('certification_requests');
        });

        Schema::table('certifications', function (Blueprint $table) {
            $table->bigInteger('certification_request_id')->nullable(false)->change();
            $table->foreign('certification_request_id')->references('id')->on('certification_requests');
        });
    }
};