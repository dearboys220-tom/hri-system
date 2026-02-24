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
        Schema::create('work_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('certification_request_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('company')->nullable();
            $table->string('position')->nullable();
            $table->string('employment_type', 50)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->text('duties')->nullable();
            $table->text('reason')->nullable();

            $table->string('supervisor_name')->nullable();
            $table->string('supervisor_contact', 50)->nullable();
            $table->string('supervisor_position')->nullable();

            $table->string('employment_certificate')->nullable();

            $table->timestamps();
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
