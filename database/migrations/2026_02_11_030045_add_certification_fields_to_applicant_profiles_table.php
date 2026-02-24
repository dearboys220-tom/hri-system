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
        Schema::table('applicant_profiles', function (Blueprint $table) {
            $table->string('member_id', 20)
                ->unique()
                ->nullable()
                ->after('user_id');

            $table->enum('certification_status', [
                'Belum Apply',
                'Sedang Investigasi',
                'Sedang Review',
                'Terverifikasi',
                'Ditolak'
            ])->default('Belum Apply')->after('member_id');

            $table->timestamp('certification_date')->nullable()->after('certification_status');
            $table->timestamp('certification_expiry_date')->nullable()->after('certification_date');
            $table->decimal('hri_score', 5, 2)->nullable()->after('certification_expiry_date');

            $table->string('profile_photo')->nullable()->after('hri_score');$table->boolean('free_certification_used')->after('profile_photo');
            $table->timestamp('free_certification_expires_at')->nullable()->after('free_certification_used');
            $table->string('nik', 50)->nullable()->after('member_id');

            $table->string('ktp_card', 20)->nullable()->after('nik');
            $table->text('ktp_address')->nullable()->after('ktp_card');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applicant_profiles', function (Blueprint $table) {
            //
        });
    }
};
