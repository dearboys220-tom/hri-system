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
        Schema::table('job_posts', function (Blueprint $table) {

            $table->text('job_description')->nullable();
            $table->date('application_deadline')->nullable();

            $table->text('required_skills')->nullable()->change();
            $table->text('preferred_skills')->nullable();

            $table->integer('application_count')->default(0)->after('views');

            $table->enum('status', [
                'draft',
                'active',
                'closed',
                'deleted'
            ])->default('draft')->change();
            $table->boolean('is_free_post')->default(true)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
