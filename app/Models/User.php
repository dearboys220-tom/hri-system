<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
    // role_type 定数（v2.8完全版）
    // -------------------------------------------------------
    const ROLE_APPLICANT       = 'applicant';
    const ROLE_COMPANY         = 'company';
    const ROLE_INVESTIGATOR    = 'investigator_user';
    const ROLE_ADMIN           = 'admin_user';
    const ROLE_EM_STAFF        = 'em_staff';
    const ROLE_LOCAL_MANAGER   = 'local_manager';
    const ROLE_PRESIDENT       = 'president';
    const ROLE_STRATEGY        = 'strategy_user';   // ★ v2.8追加
    const ROLE_AI_DEV          = 'ai_dev_user';     // ★ v2.8追加
    const ROLE_MARKETING       = 'marketing_user';  // ★ v2.8追加
    const ROLE_SUPER_ADMIN     = 'super_admin';

    // 社内スタッフロール一覧（v2.8完全版）
    const STAFF_ROLES = [
        'investigator_user',
        'admin_user',
        'em_staff',
        'local_manager',
        'president',
        'strategy_user',
        'ai_dev_user',
        'marketing_user',
        'super_admin',
    ];

    // 管理者ロール一覧
    const MANAGER_ROLES = [
        'local_manager',
        'president',
        'super_admin',
    ];

    // -------------------------------------------------------
    // リレーション（既存 — 一般会員）
    // -------------------------------------------------------

    public function applicantProfile(): HasOne
    {
        return $this->hasOne(ApplicantProfile::class);
    }

    public function companyProfile(): HasOne
    {
        return $this->hasOne(CompanyProfile::class);
    }

    // -------------------------------------------------------
    // リレーション（v2.5追加 — PDP法対応・ログ）
    // -------------------------------------------------------

    public function consentRecords(): HasMany
    {
        return $this->hasMany(ConsentRecord::class);
    }

    public function deletionRequests(): HasMany
    {
        return $this->hasMany(DataDeletionRequest::class);
    }

    public function staffActivityLogs(): HasMany
    {
        return $this->hasMany(StaffActivityLog::class);
    }

    // -------------------------------------------------------
    // リレーション（v2.8追加 — 社員管理系）
    // -------------------------------------------------------

    public function staffProfile(): HasOne
    {
        return $this->hasOne(StaffProfile::class);
    }

    public function staffAvailability(): HasOne
    {
        return $this->hasOne(StaffAvailability::class, 'staff_user_id');
    }

    public function absenceRequests(): HasMany
    {
        return $this->hasMany(EmployeeAbsenceRequest::class, 'staff_user_id');
    }

    public function staffEvaluations(): HasMany
    {
        return $this->hasMany(StaffEvaluation::class, 'staff_user_id');
    }

    public function salaryCalculations(): HasMany
    {
        return $this->hasMany(SalaryCalculation::class, 'staff_user_id');
    }

    public function payrollRecords(): HasMany
    {
        return $this->hasMany(PayrollRecord::class, 'staff_user_id');
    }

    // AI社員管理系
    public function taskAssignments(): HasMany
    {
        return $this->hasMany(AiTaskAssignment::class, 'assignee_user_id');
    }

    public function taskOrders(): HasMany
    {
        return $this->hasMany(AiTaskOrder::class, 'ordered_by_user_id');
    }

    // -------------------------------------------------------
    // ヘルパー — ロール判定（既存）
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

    // -------------------------------------------------------
    // ヘルパー — ロール判定（v2.8追加）
    // -------------------------------------------------------

    /** 社内スタッフかどうか（全スタッフロール） */
    public function isStaff(): bool
    {
        return in_array($this->role_type, self::STAFF_ROLES);
    }

    /** 管理者権限を持つか（local_manager / president / super_admin） */
    public function isManager(): bool
    {
        return in_array($this->role_type, self::MANAGER_ROLES);
    }

    public function isLocalManager(): bool
    {
        return $this->role_type === self::ROLE_LOCAL_MANAGER;
    }

    public function isPresident(): bool
    {
        return $this->role_type === self::ROLE_PRESIDENT;
    }

    public function isEmStaff(): bool
    {
        return $this->role_type === self::ROLE_EM_STAFF;
    }

    public function isStrategyUser(): bool
    {
        return $this->role_type === self::ROLE_STRATEGY;
    }

    public function isAiDevUser(): bool
    {
        return $this->role_type === self::ROLE_AI_DEV;
    }

    public function isMarketingUser(): bool
    {
        return $this->role_type === self::ROLE_MARKETING;
    }

    // -------------------------------------------------------
    // ヘルパー — その他（既存）
    // -------------------------------------------------------

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

    // -------------------------------------------------------
    // ヘルパー — 稼働状態（v2.8追加）
    // -------------------------------------------------------

    /** スタッフが現在対応可能か確認 */
    public function isAvailableForTask(): bool
    {
        return $this->staffAvailability?->isAvailable() ?? false;
    }

    /** スタッフプロフィールの部署コードを取得 */
    public function getDepartmentCode(): ?string
    {
        return $this->staffProfile?->department_code;
    }
}