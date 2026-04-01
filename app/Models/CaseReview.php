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
    ];

    protected $casts = [
        'raw_response_json'     => 'array',
        'verified_items_json'   => 'array',
        'unverified_items_json' => 'array',
        'risk_flags_json'       => 'array',
        'conditions_json'       => 'array',
        'compliance_return_json'=> 'array',
        'reviewed_at'           => 'datetime',
        'base_score'            => 'decimal:2',
        'truthfulness_percent'  => 'decimal:2',
        'consistency_percent'   => 'decimal:2',
        'hri_suitability_score' => 'decimal:2',
        'work_ability_score'    => 'decimal:2',
    ];

    // ===== リレーション =====

    public function certificationRequest()
    {
        return $this->belongsTo(CertificationRequest::class);
    }

    public function reviewItems()
    {
        return $this->hasMany(ReviewItem::class);
    }

    // ===== 判定定数 =====

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
}