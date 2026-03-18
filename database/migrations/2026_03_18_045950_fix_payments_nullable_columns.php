<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('transaction_id')->nullable()->change();
            $table->string('midtrans_order_id')->nullable()->change();
            $table->string('midtrans_transaction_id')->nullable()->change();
            $table->string('midtrans_snap_token')->nullable()->change();
            $table->string('payment_method')->nullable()->change();
        });
    }

    public function down(): void {}
};