<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkHistory extends Model
{
    protected $table = 'work_history';
    protected $fillable = [
        'user_id',
        'certification_request_id',
        'company',
        'company_address',
        'position',
        'employment_type',
        'start_date',
        'end_date',
        'duties',
        'resignation_reason',
        'achievements',
        'reason',
        'supervisor_name',
        'supervisor_contact',
        'supervisor_position',
        'employment_certificate',
    ];
}