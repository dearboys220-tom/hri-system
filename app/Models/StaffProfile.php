<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StaffProfile extends Model
{
    protected $fillable = [
        'user_id',
        'full_name',
        'employee_id',
        'role_type',
        'department_code',
        'position_title',
        'phone',
        'whatsapp',
        'emergency_contact_name',
        'emergency_contact_phone',
        'bank_name',
        'bank_account_no',
        'bank_account_name',
        'join_date',
        'contract_end_date',
        'contract_type',
        'base_salary',
        'profile_photo_path',
        'skills_json',
        'education_completed',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'join_date'          => 'date',
        'contract_end_date'  => 'date',
        'base_salary'        => 'decimal:2',
        'is_active'          => 'boolean',
        'skills_json'        => 'array',
        'education_completed'=> 'array',
    ];

    // ---- リレーション ----

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function availability(): HasOne
    {
        return $this->hasOne(StaffAvailability::class, 'staff_user_id', 'user_id');
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(StaffEvaluation::class, 'staff_user_id', 'user_id');
    }

    public function salaryCalculations(): HasMany
    {
        return $this->hasMany(SalaryCalculation::class, 'staff_user_id', 'user_id');
    }

    public function absenceRequests(): HasMany
    {
        return $this->hasMany(EmployeeAbsenceRequest::class, 'staff_user_id', 'user_id');
    }
}