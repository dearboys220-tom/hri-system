<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkHistory extends Model
{
    protected $table = 'work_history';

    protected $fillable = [
        'user_id',
        'certification_request_id',
        'company_name',
        'company_address',
        'department_position',
        'employment_type',
        'employment_start_date',
        'employment_end_date',
        'job_description',
        'resignation_reason',
        'employment_achievements',
        'reason',
        'supervisor_full_name',
        'supervisor_phone',
        'supervisor_position',
        'employment_certificate',
    ];
}