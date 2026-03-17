<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    protected $fillable = [
        'job_post_id',
        'applicant_id',
        'company_id',
        'status',
        'applied_at',
        'company_notes',
        'applicant_snapshot',
        'job_deleted',
    ];

    protected $casts = [
        'applicant_snapshot' => 'array',
        'job_deleted'        => 'boolean',
    ];
}
