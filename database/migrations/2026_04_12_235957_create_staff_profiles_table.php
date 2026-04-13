<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('full_name');
            $table->string('employee_id')->unique()->nullable(); // 社員番号
            $table->string('role_type');                          // users.role_type と同期
            $table->string('department_code')->nullable();        // departments.dept_code と同期
            $table->string('position_title')->nullable();         // 役職名
            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_no')->nullable();
            $table->string('bank_account_name')->nullable();
            $table->date('join_date')->nullable();
            $table->date('contract_end_date')->nullable();
            $table->string('contract_type')->default('PERMANENT'); // PERMANENT / CONTRACT / FREELANCE
            $table->decimal('base_salary', 15, 2)->nullable();     // 基本給（IDR）
            $table->string('profile_photo_path')->nullable();
            $table->text('skills_json')->nullable();               // スキルタグ（JSON配列）
            $table->string('education_completed')->nullable();     // 教育受講状態（JSON）
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_profiles');
    }
};