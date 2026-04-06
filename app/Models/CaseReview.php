<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaseReview extends Model
{
    protected $fillable = [
        'certification_request_id',
        'prompt_version',
        'model_name',
        'raw_response_json',
        'final_decision',
        'base_score',
        'truthfulness_percent',
        'consistency_percent',
        'hri_suitability_score',
        'work_ability_score',
        'work_ability_band',
        'claude_overall_judgment',
        'claude_overall_reason',
        'enterprise_view_summary',
        'summary',
        'verified_items_json',
        'unverified_items_json',
        'risk_flags_json',
        'conditions_json',
        'compliance_return_json',
        'reviewed_at',
        // v2.6追加
        'case_no',
        'input_hash',
        'confidence_level',
        'ai_proposed_decision',
        'human_override_decision',
        'human_override_reason',
        'effective_decision',
        'approved_by_user_id',
        'approved_at',
    ];

    protected $casts = [
        'raw_response_json'      => 'array',
        'verified_items_json'    => 'array',
        'unverified_items_json'  => 'array',
        'risk_flags_json'        => 'array',
        'conditions_json'        => 'array',
        'compliance_return_json' => 'array',
        'reviewed_at'            => 'datetime',
        'base_score'             => 'decimal:2',
        'truthfulness_percent'   => 'decimal:2',
        'consistency_percent'    => 'decimal:2',
        'hri_suitability_score'  => 'decimal:2',
        'work_ability_score'     => 'decimal:2',
        // v2.6追加
        'approved_at'            => 'datetime',
    ];

    // -------------------------------------------------------
    // 判定定数（既存）
    // -------------------------------------------------------
    const FINAL_DECISIONS = [
        'APPROVE',
        'CONDITIONAL_APPROVE',
        'REJECT',
        'ESCALATE_TO_HUMAN',
        'RETURN_TO_INVESTIGATION',
    ];

    const JUDGMENT_LABELS = [
        'STRONGLY_RECOMMENDED_FOR_VERIFIED_VIEW',
        'VERIFIED_WITH_RESERVATIONS',
        'LIMITED_RELIABILITY',
        'HUMAN_REVIEW_STRONGLY_RECOMMENDED',
        'RETURN_REQUIRED',
    ];

    const WORK_ABILITY_BANDS = [
        'HIGH'     => ['min' => 85, 'max' => 100],
        'MODERATE' => ['min' => 70, 'max' => 84],
        'LIMITED'  => ['min' => 55, 'max' => 69],
        'LOW'      => ['min' => 0,  'max' => 54],
    ];

    // decision 定数（v2.6追加）
    const DECISION_APPROVE                 = 'APPROVE';
    const DECISION_CONDITIONAL_APPROVE     = 'CONDITIONAL_APPROVE';
    const DECISION_REJECT                  = 'REJECT';
    const DECISION_ESCALATE_TO_HUMAN       = 'ESCALATE_TO_HUMAN';
    const DECISION_RETURN_TO_INVESTIGATION = 'RETURN_TO_INVESTIGATION';

    // -------------------------------------------------------
    // リレーション（既存）
    // -------------------------------------------------------
    public function certificationRequest()
    {
        return $this->belongsTo(CertificationRequest::class);
    }

    public function reviewItems()
    {
        return $this->hasMany(ReviewItem::class);
    }

    // -------------------------------------------------------
    // リレーション（v2.6追加）
    // -------------------------------------------------------
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    // -------------------------------------------------------
    // ヘルパー（v2.6追加）
    // -------------------------------------------------------

    /**
     * AI提案通りに確定（審査管理部が同意した場合）
     * human_override_decision は null のまま
     */
    public function confirmByHuman(int $staffUserId): void
    {
        $this->update([
            'human_override_decision' => null,
            'effective_decision'      => $this->ai_proposed_decision,
            'approved_by_user_id'     => $staffUserId,
            'approved_at'             => now(),
        ]);
    }

    /**
     * 審査管理部が判定を上書きする
     * effective_decision = human_override_decision に更新
     */
    public function overrideByHuman(
        int    $staffUserId,
        string $decision,
        string $reason
    ): void {
        $this->update([
            'human_override_decision' => $decision,
            'human_override_reason'   => $reason,
            'effective_decision'      => $decision,
            'approved_by_user_id'     => $staffUserId,
            'approved_at'             => now(),
        ]);
    }

    /** AI提案のみで確定したか（人間の上書きなし） */
    public function isAiDecision(): bool
    {
        return $this->human_override_decision === null
            && $this->effective_decision !== null;
    }
}
