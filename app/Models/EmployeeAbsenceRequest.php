<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeAbsenceRequest extends Model
{
    protected $fillable = [
        'staff_user_id',
        'absence_type',
        'absence_date_from',
        'absence_date_to',
        'absence_days',
        'reason',
        'supporting_doc_path',
        'approval_status',
        'approved_by_user_id',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'absence_date_from' => 'date',
        'absence_date_to'   => 'date',
        'approved_at'       => 'datetime',
    ];

    // ステータス定数
    const STATUS_PENDING  = 'PENDING';
    const STATUS_APPROVED = 'APPROVED';
    const STATUS_REJECTED = 'REJECTED';

    // 欠勤種別定数
    const TYPE_SICK         = 'SICK';
    const TYPE_PERSONAL     = 'PERSONAL';
    const TYPE_ANNUAL_LEAVE = 'ANNUAL_LEAVE';
    const TYPE_EMERGENCY    = 'EMERGENCY';
    const TYPE_OTHER        = 'OTHER';

    // ---- リレーション ----

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_user_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }
}