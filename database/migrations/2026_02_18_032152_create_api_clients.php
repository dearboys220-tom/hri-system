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
        Schema::create('api_clients', function (Blueprint $table) {
            $table->id();

            $table->foreignId('region_id')
                ->constrained('regions')
                ->cascadeOnDelete();

            $table->string('client_name');

            $table->string('api_key')->unique();

            $table->string('api_secret');

            $table->string('plan_type');

            $table->integer('monthly_limit')->nullable();

            $table->integer('used_this_month')->default(0);

            $table->boolean('is_active')->default(true)->index();

            $table->date('contract_start')->nullable();
            $table->date('contract_end')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_clients');
    }
};
