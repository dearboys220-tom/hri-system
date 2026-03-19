<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_posts', function (Blueprint $table) {
            $table->string('workplace_photo')->nullable()->after('title');
            $table->string('experience_level', 100)->nullable()->after('education_requirement');
            $table->json('working_days')->nullable()->after('experience_level');
            $table->string('working_hours', 100)->nullable()->after('working_days');
            $table->json('language_requirements')->nullable()->after('working_hours');
            $table->string('gender', 50)->nullable()->after('language_requirements');
            $table->integer('age_min')->nullable()->after('gender');
            $table->integer('age_max')->nullable()->after('age_min');
            $table->string('marital_status', 50)->nullable()->after('age_max');
            $table->date('start_date')->nullable()->after('application_deadline');
            $table->text('special_requirements')->nullable()->after('start_date');
        });
    }

    public function down(): void {}
};