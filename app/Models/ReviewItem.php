<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewItem extends Model
{
    protected $fillable = [
        'certification_request_id',
        'case_review_id',
        'category',
        'item_name',
        // 旧カラム（減点方式・互換性のため残す）
        'max_deduction',
        'actual_deduction',
        'weight',
        'notes',
        'ai_reasoning',
        'is_ai_scored',
        // v2.4 加点方式
        'max_score',
        'actual_score',
        'score_reason',
        'evidence_summary',
        'verification_status',
        'ai_model',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at'  => 'datetime',
        'is_ai_scored' => 'boolean',
        'max_score'    => 'integer',
        'actual_score' => 'integer',
    ];

    // ===== リレーション =====

    public function certificationRequest()
    {
        return $this->belongsTo(CertificationRequest::class);
    }

    public function caseReview()
    {
        return $this->belongsTo(CaseReview::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // ===== カテゴリ定数 =====

    const CATEGORIES = [
        'identity'      => ['max_score' => 20, 'label' => 'Informasi Dasar & Identitas'],
        'education'     => ['max_score' => 15, 'label' => 'Riwayat Pendidikan'],
        'work'          => ['max_score' => 25, 'label' => 'Riwayat Pekerjaan'],
        'certification' => ['max_score' => 10, 'label' => 'Sertifikat & Keahlian'],
        'conduct'       => ['max_score' => 20, 'label' => 'Perilaku Kerja'],
        'consistency'   => ['max_score' => 10, 'label' => 'Konsistensi Keseluruhan'],
    ];

    const VERIFICATION_STATUSES = [
        'VERIFIED',
        'PARTIALLY_VERIFIED',
        'UNVERIFIED',
        'CONTRADICTED',
    ];
}