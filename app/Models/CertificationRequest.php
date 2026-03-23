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
        'ready_for_review'      => 'boolean',
        'admin_approved'        => 'boolean',
        'returned_to_applicant' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function applicantProfile()
    {
        return $this->hasOneThrough(
            ApplicantProfile::class,
            User::class,
            'id',        // users.id
            'user_id',   // applicant_profiles.user_id
            'user_id',   // certification_requests.user_id
            'id'         // users.id
        );
    }

    public function educationHistory()
    {
        return $this->hasMany(EducationHistory::class);
    }

    public function workHistory()
    {
        return $this->hasMany(WorkHistory::class);
    }

    public function certifications()
    {
        return $this->hasMany(Certification::class);
    }

    public function investigationItems()
    {
        return $this->hasMany(InvestigationItem::class);
    }

    public function investigator()
    {
        return $this->belongsTo(User::class, 'assigned_investigator');
    }

    public function reviewItems()
    {
        return $this->hasMany(ReviewItem::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'assigned_reviewer');
    }
}