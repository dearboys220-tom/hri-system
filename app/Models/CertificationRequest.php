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
        // v2.6
        'case_no',
        'current_status',
        'external_status',
        'latest_return_id',
        'internal_return_required',
        'deliverable_vr_status',
        'deliverable_ir_status',
        'deliverable_rn_status',
        'approved_by_user_id',
        'approved_at',
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
        // v2.6
        'internal_return_required'   => 'boolean',
        'approved_at'                => 'datetime',
    ];

    // -------------------------------------------------------
    // current_status 定数
    // -------------------------------------------------------
    const STATUS_DRAFT                  = 'draft';
    const STATUS_UNDER_INVESTIGATION    = 'under_investigation';
    const STATUS_AI_REVIEW_PENDING      = 'ai_review_pending';
    const STATUS_RETURNED_INTERNAL      = 'returned_internal';
    const STATUS_HUMAN_REVIEW           = 'human_review_required';
    const STATUS_CONDITIONALLY_VERIFIED = 'conditionally_verified';
    const STATUS_VERIFIED               = 'verified';
    const STATUS_REJECTED               = 'rejected';

    // external_status 定数
    const EXT_UNDER_REVIEW           = 'under_review';
    const EXT_ADDITIONAL_CHECK       = 'additional_check_in_progress';
    const EXT_CONDITIONALLY_VERIFIED = 'conditionally_verified';
    const EXT_VERIFIED               = 'verified';
    const EXT_REJECTED               = 'rejected';

    // -------------------------------------------------------
    // boot: 新規作成時に case_no を自動採番
    // -------------------------------------------------------
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $request) {
            if (empty($request->case_no)) {
                $request->case_no = app(\App\Services\CaseNoService::class)->generate();
            }
        });
    }

    // -------------------------------------------------------
    // リレーション（既存）
    // -------------------------------------------------------
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

    // -------------------------------------------------------
    // リレーション（v2.6追加）
    // -------------------------------------------------------
    public function latestReturn()
    {
        return $this->belongsTo(CaseReturn::class, 'latest_return_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function deliverables()
    {
        return $this->hasMany(CaseDeliverable::class);
    }

    public function caseReturns()
    {
        return $this->hasMany(CaseReturn::class);
    }

    public function priorityReport()
    {
        return $this->hasOne(InvestigationPriorityReport::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class, 'case_no', 'case_no');
    }

    public function consentRecords()
    {
        return $this->hasMany(ConsentRecord::class, 'case_no', 'case_no');
    }

    // -------------------------------------------------------
    // ヘルパー（既存）
    // -------------------------------------------------------
    public function hasConductItems(): bool
    {
        return $this->investigationItems()
            ->where('category', 'conduct')
            ->exists();
    }

    // -------------------------------------------------------
    // ヘルパー（v2.6追加）
    // -------------------------------------------------------

    /**
     * アクティブなRNが存在するか（VR/IR発行前チェック用）
     * CaseDeliverableService::issueAll() 内で使用する
     */
    public function hasActiveReturnNotice(): bool
    {
        return $this->deliverables()
            ->where('deliverable_type', CaseDeliverable::TYPE_RN)
            ->where('deliverable_status', CaseDeliverable::STATUS_ISSUED)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * current_status と external_status を同時に更新する。
     * ⚠️ ステータス変更は必ずこのメソッド経由で行うこと。
     *    直接 update(['survey_status' => ...]) は使わない。
     */
    public function updateStatus(string $currentStatus, string $externalStatus): void
    {
        $this->update([
            'current_status' => $currentStatus,
            'external_status' => $externalStatus,
            // 後方互換用（既存の survey_status と同期）
            'survey_status'  => $currentStatus,
        ]);
    }
}
