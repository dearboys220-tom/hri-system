<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_type',
        'google_id',
        'google_token',
        'email_verified_at',
        'agreed_terms_at',
        'agreed_investigation_at',
        'terms_version',
        // v2.5追加
        'status',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'last_login_at'     => 'datetime',
        ];
    }

    // -------------------------------------------------------
    // role_type 定数
    // -------------------------------------------------------
    const ROLE_APPLICANT    = 'applicant';
    const ROLE_COMPANY      = 'company';
    const ROLE_INVESTIGATOR = 'investigator_user';
    const ROLE_ADMIN        = 'admin_user';
    const ROLE_SUPER_ADMIN  = 'super_admin';

    // -------------------------------------------------------
    // リレーション（既存）
    // -------------------------------------------------------
    public function applicantProfile()
    {
        return $this->hasOne(ApplicantProfile::class);
    }

    public function companyProfile()
    {
        return $this->hasOne(CompanyProfile::class);
    }

    // -------------------------------------------------------
    // リレーション（v2.5追加）
    // -------------------------------------------------------
    public function consentRecords()
    {
        return $this->hasMany(ConsentRecord::class);
    }

    public function deletionRequests()
    {
        return $this->hasMany(DataDeletionRequest::class);
    }

    public function staffActivityLogs()
    {
        return $this->hasMany(StaffActivityLog::class);
    }

    // -------------------------------------------------------
    // ヘルパー
    // -------------------------------------------------------
    public function isSuperAdmin(): bool
    {
        return $this->role_type === self::ROLE_SUPER_ADMIN;
    }

    public function isInvestigator(): bool
    {
        return $this->role_type === self::ROLE_INVESTIGATOR;
    }

    public function isAdmin(): bool
    {
        return $this->role_type === self::ROLE_ADMIN;
    }

    public function isApplicant(): bool
    {
        return $this->role_type === self::ROLE_APPLICANT;
    }

    public function isCompany(): bool
    {
        return $this->role_type === self::ROLE_COMPANY;
    }

    /** 最終ログイン情報を更新（ログイン成功時に呼び出す） */
    public function updateLastLogin(): void
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => request()->ip(),
        ]);
    }

    /** 指定の同意種別で有効な同意を持つか確認 */
    public function hasActiveConsent(string $consentType): bool
    {
        return ConsentRecord::hasActiveConsent($this->id, $consentType);
    }
}
