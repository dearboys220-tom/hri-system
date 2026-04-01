<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CertificationRequest extends Model
{
    protected $fillable = [
        'user_id',
        'survey_status',
        'investigation_status',
        'evaluation_score',
        'weighted_score',
        'total_deductions',
        'request_date',
        'final_approval_date',
        'approved_by',
        'rejected_by',
        'rejection_reason',
        'assigned_investigator',
        'investigation_started_at',
        'investigation_completed_at',
        'investigation_notes',
        'ready_for_review',
        'assigned_reviewer',
        'review_started_at',
        'review_completed_at',
        'reviewer_comments',
        'review_completed_date',
        'admin_approved',
        'admin_approval_date',
        'admin_notes',
        'returned_to_applicant',
        'return_reason',
        'payment_id',
        // v2.4
        'ai_review_completed_at',
        'ai_model_name',
        'ai_prompt_version',
        'latest_case_review_id',
        'base_score',
        'truthfulness_percent',
        'consistency_percent',
        'hri_suitability_score',
        'work_ability_score',
        'work_ability_band',
        'claude_overall_judgment',
        'claude_overall_reason',
        'enterprise_view_summary',
        'final_decision',
    ];

    protected $casts = [
        'request_date'               => 'datetime',
        'final_approval_date'        => 'datetime',
        'investigation_started_at'   => 'datetime',
        'investigation_completed_at' => 'datetime',
        'review_started_at'          => 'datetime',
        'review_completed_at'        => 'datetime',
        'admin_approval_date'        => 'datetime',
        'ai_review_completed_at'     => 'datetime',
        'ready_for_review'           => 'boolean',
        'admin_approved'             => 'boolean',
        'returned_to_applicant'      => 'boolean',
        'base_score'                 => 'decimal:2',
        'truthfulness_percent'       => 'decimal:2',
        'consistency_percent'        => 'decimal:2',
        'hri_suitability_score'      => 'decimal:2',
        'work_ability_score'         => 'decimal:2',
    ];

    // ===== リレーション =====

    public function applicant()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function investigator()
    {
        return $this->belongsTo(User::class, 'assigned_investigator');
    }

    public function educationHistories()
    {
        return $this->hasMany(EducationHistory::class);
    }

    public function workHistories()
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

    public function reviewItems()
    {
        return $this->hasMany(ReviewItem::class);
    }

    public function caseReviews()
    {
        return $this->hasMany(CaseReview::class);
    }

    public function latestCaseReview()
    {
        return $this->belongsTo(CaseReview::class, 'latest_case_review_id');
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    // ===== ヘルパー =====

    public function isConduct완료(): bool
    {
        return $this->investigationItems()
            ->where('category', 'conduct')
            ->exists();
    }

    public function hasConductItems(): bool
    {
        return $this->investigationItems()
            ->where('category', 'conduct')
            ->exists();
    }
}