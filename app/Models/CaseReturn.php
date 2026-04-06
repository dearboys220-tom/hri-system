<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaseReturn extends Model
{
    protected $fillable = [
        'case_no',
        'certification_request_id',
        'case_review_id',
        'return_reason_code',
        'return_reason_summary',
        'violation_types_json',
        'error_points_json',
        'missing_points_json',
        'incorrect_points_json',
        'reinvestigation_instructions_json',
        'prohibition_warning_json',
        'resubmission_requirements_json',
        'returned_at',
        'resolved_at',
        'resolved_by',
    ];

    protected $casts = [
        'violation_types_json'              => 'array',
        'error_points_json'                 => 'array',
        'missing_points_json'               => 'array',
        'incorrect_points_json'             => 'array',
        'reinvestigation_instructions_json' => 'array',
        'prohibition_warning_json'          => 'array',
        'resubmission_requirements_json'    => 'array',
        'returned_at'                       => 'datetime',
        'resolved_at'                       => 'datetime',
    ];

    // return_reason_code 定数
    const CODE_PROHIBITION_VIOLATION    = 'PROHIBITION_VIOLATION';
    const CODE_INCOMPLETE_INVESTIGATION = 'INCOMPLETE_INVESTIGATION';
    const CODE_CONTRADICTION            = 'CONTRADICTION';
    const CODE_INSUFFICIENT_EVIDENCE    = 'INSUFFICIENT_EVIDENCE';
    const CODE_POLICY_VIOLATION         = 'POLICY_VIOLATION';

    // -------------------------------------------------------
    // ⚠️ このモデルのデータは絶対に外部（applicant/company）に返さない
    // APIリソース・Bladeテンプレートでの使用時は必ず確認すること
    // -------------------------------------------------------

    // -------------------------------------------------------
    // リレーション
    // -------------------------------------------------------
    public function certificationRequest(): BelongsTo
    {
        return $this->belongsTo(CertificationRequest::class);
    }

    public function caseReview(): BelongsTo
    {
        return $this->belongsTo(CaseReview::class);
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    // -------------------------------------------------------
    // スコープ
    // -------------------------------------------------------

    /** 未解消の差し戻しのみ */
    public function scopeUnresolved($query)
    {
        return $query->whereNull('resolved_at');
    }

    // -------------------------------------------------------
    // ヘルパー
    // -------------------------------------------------------
    public function isResolved(): bool
    {
        return $this->resolved_at !== null;
    }

    /** 差し戻しを解消する */
    public function resolve(int $resolvedByUserId): void
    {
        $this->update([
            'resolved_at' => now(),
            'resolved_by' => $resolvedByUserId,
        ]);
    }
}
