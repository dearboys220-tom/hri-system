<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeAbsenceRequest extends Model
{
    use HasFactory;

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
        'absence_days'      => 'integer',
    ];

    public function staffUser()
    {
        return $this->belongsTo(User::class, 'staff_user_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }
}