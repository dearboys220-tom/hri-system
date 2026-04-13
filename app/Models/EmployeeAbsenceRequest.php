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
        'start_date',
        'end_date',
        'reason',
        'document_url',
        'approval_status',
        'approved_by_user_id',
        'approved_at',
        'manager_note',
    ];

    protected $casts = [
        'start_date'  => 'date',
        'end_date'    => 'date',
        'approved_at' => 'datetime',
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