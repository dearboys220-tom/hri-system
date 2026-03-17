<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CertificationRequest extends Model
{
    protected $fillable = [
        'user_id',
        'survey_status',
        'evaluation_score',
        'weighted_score',
        'assigned_investigator',
        'investigation_notes',
        'ready_for_review',
        'assigned_reviewer',
        'reviewer_comments',
        'review_completed_date',
        'admin_approved',
        'admin_approval_date',
        'admin_notes',
        'returned_to_applicant',
        'return_reason',
        'payment_id',
        'region_id',
    ];

    protected $casts = [
        'ready_for_review'       => 'boolean',
        'admin_approved'         => 'boolean',
        'returned_to_applicant'  => 'boolean',
    ];
}