<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ============================================================
        // education_history：リネームのみ（追加フィールドは既存）
        // ============================================================
        Schema::table('education_history', function (Blueprint $table) {
            $table->renameColumn('school',       'school_name');
            $table->renameColumn('level',        'education_level');
            $table->renameColumn('major',        'degree_name');
            $table->renameColumn('gpa',          'ipk_gpa');
            $table->renameColumn('achievements', 'academic_achievements');
        });

        // ============================================================
        // work_history：リネームのみ（追加フィールドは既存）
        // ============================================================
        Schema::table('work_history', function (Blueprint $table) {
            $table->renameColumn('company',            'company_name');
            $table->renameColumn('position',           'department_position');
            $table->renameColumn('start_date',         'employment_start_date');
            $table->renameColumn('end_date',           'employment_end_date');
            $table->renameColumn('duties',             'job_description');
            $table->renameColumn('supervisor_name',    'supervisor_full_name');
            $table->renameColumn('supervisor_contact', 'supervisor_phone');
            $table->renameColumn('achievements',       'employment_achievements');
        });

        // ============================================================
        // certifications：リネーム + certificate_attachment 追加
        // ============================================================
        Schema::table('certifications', function (Blueprint $table) {
            $table->renameColumn('name',         'certificate_name');
            $table->renameColumn('organization', 'issuing_organization');
            $table->renameColumn('issued_date',  'issue_date');
            $table->renameColumn('valid_until',  'expiration_date');
            $table->renameColumn('notes',        'certificate_notes');
        });

        Schema::table('certifications', function (Blueprint $table) {
            // certificate_attachment のみ新規追加
            $table->string('certificate_attachment')->nullable()->after('certificate_notes');
        });
    }

    public function down(): void
    {
        // education_history
        Schema::table('education_history', function (Blueprint $table) {
            $table->renameColumn('school_name',           'school');
            $table->renameColumn('education_level',       'level');
            $table->renameColumn('degree_name',           'major');
            $table->renameColumn('ipk_gpa',               'gpa');
            $table->renameColumn('academic_achievements', 'achievements');
        });

        // work_history
        Schema::table('work_history', function (Blueprint $table) {
            $table->renameColumn('company_name',          'company');
            $table->renameColumn('department_position',   'position');
            $table->renameColumn('employment_start_date', 'start_date');
            $table->renameColumn('employment_end_date',   'end_date');
            $table->renameColumn('job_description',       'duties');
            $table->renameColumn('supervisor_full_name',  'supervisor_name');
            $table->renameColumn('supervisor_phone',      'supervisor_contact');
            $table->renameColumn('employment_achievements','achievements');
        });

        // certifications
        Schema::table('certifications', function (Blueprint $table) {
            $table->renameColumn('certificate_name',    'name');
            $table->renameColumn('issuing_organization','organization');
            $table->renameColumn('issue_date',          'issued_date');
            $table->renameColumn('expiration_date',     'valid_until');
            $table->renameColumn('certificate_notes',   'notes');
            $table->dropColumn('certificate_attachment');
        });
    }
};