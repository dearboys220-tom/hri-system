<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SalaryCalculation extends Model
{
    protected $fillable = [
        'staff_user_id',
        'calculation_month',
        'base_salary',
        'attendance_days',
        'absent_days',
        'task_completion_rate',
        'performance_band',
        'performance_adjustment',
        'deductions',
        'overtime_pay',
        'allowances',
        'gross_salary',
        'net_salary',
        'calculation_status',
        'ai_calculation_notes',
        'approved_by_user_id',
        'approved_at',
    ];

    protected $casts = [
        'base_salary'            => 'decimal:2',
        'performance_adjustment' => 'decimal:2',
        'deductions'             => 'decimal:2',
        'overtime_pay'           => 'decimal:2',
        'allowances'             => 'decimal:2',
        'gross_salary'           => 'decimal:2',
        'net_salary'             => 'decimal:2',
        'task_completion_rate'   => 'decimal:2',
        'approved_at'            => 'datetime',
    ];

    // ステータス定数
    const STATUS_DRAFT            = 'DRAFT';
    const STATUS_PENDING_APPROVAL = 'PENDING_APPROVAL';
    const STATUS_APPROVED         = 'APPROVED';
    const STATUS_REJECTED         = 'REJECTED';

    // ---- リレーション ----

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_user_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function payrollRecord(): HasOne
    {
        return $this->hasOne(PayrollRecord::class);
    }

    public function evaluation(): HasOne
    {
        return $this->hasOne(StaffEvaluation::class);
    }

    // ---- 総支給額・手取り自動計算 ----

    public function recalculate(): void
    {
        $this->gross_salary = $this->base_salary
            + $this->performance_adjustment
            + $this->overtime_pay
            + $this->allowances;

        $this->net_salary = $this->gross_salary - $this->deductions;
        $this->save();
    }
}