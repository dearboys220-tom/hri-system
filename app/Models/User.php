<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Enums\RoleType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'google_token',
        'role_type',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function applicantProfile()
    {
        return $this->hasOne(ApplicantProfile::class);
    }
    public function companyProfile()
    {
        return $this->hasOne(CompanyProfile::class);
    }


    public function syncRoleWithSpatie(): void
    {
        if ($this->role_type) {
            $this->syncRoles([$this->role_type]);
        }
    }

    public function isApplicant(): bool
    {
        return $this->role_type === RoleType::APPLICANT;
    }
    public function isCompany(): bool
    {
        return $this->role_type === RoleType::COMPANY;
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
