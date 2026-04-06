<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CaseDeliverable extends Model
{
    protected $fillable = [
        'case_no',
        'certification_request_id',
        'case_review_id',
        'deliverable_type',
        'serial_no',
        'revision_no',
        'visibility_scope',
        'deliverable_status',
        'generated_at',
        'expires_at',
        'file_path',
        'json_payload',
        'is_active',
    ];

    protected $casts = [
        'generated_at'  => 'datetime',
        'expires_at'    => 'datetime',
        'json_payload'  => 'array',
        'is_active'     => 'boolean',
    ];

    // deliverable_type 定数
    const TYPE_VR = 'VR';
    const TYPE_IR = 'IR';
    const TYPE_RN = 'RN';

    // deliverable_status 定数
    const STATUS_NOT_READY = 'NOT_READY';
    const STATUS_PENDING   = 'PENDING';
    const STATUS_ISSUED    = 'ISSUED';
    const STATUS_VOID      = 'VOID';

    // visibility_scope 定数
    const SCOPE_APPLICANT_VIEW = 'APPLICANT_VIEW';
    const SCOPE_COMPANY_VIEW   = 'COMPANY_VIEW';
    const SCOPE_INTERNAL_ONLY  = 'INTERNAL_ONLY';

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

    public function enterpriseViews(): HasMany
    {
        return $this->hasMany(EnterpriseView::class, 'deliverable_id');
    }

    // -------------------------------------------------------
    // スコープ
    // -------------------------------------------------------
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('deliverable_type', $type);
    }

    public function scopeIssued($query)
    {
        return $query->where('deliverable_status', self::STATUS_ISSUED);
    }

    // -------------------------------------------------------
    // ★ 最重要ビジネスロジック: RN保留チェック
    // VR/IR を ISSUED にできるか判定する
    // active な RN が存在する場合は発行不可
    // -------------------------------------------------------
    public static function canIssue(int $certRequestId, string $type): bool
    {
        if (! in_array($type, [self::TYPE_VR, self::TYPE_IR])) {
            return true; // RN 自体は制限なし
        }

        $activeRn = static::where('certification_request_id', $certRequestId)
            ->where('deliverable_type', self::TYPE_RN)
            ->where('deliverable_status', self::STATUS_ISSUED)
            ->where('is_active', true)
            ->exists();

        return ! $activeRn; // RNが存在する間は発行不可
    }

    // -------------------------------------------------------
    // ヘルパー
    // -------------------------------------------------------

    /** 有効期限切れかどうか */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /** VR/IR を ISSUED に移行（RNチェック付き） */
    public function issue(): bool
    {
        if (! static::canIssue($this->certification_request_id, $this->deliverable_type)) {
            return false; // active な RN が存在するため発行不可
        }

        $this->update([
            'deliverable_status' => self::STATUS_ISSUED,
            'generated_at'       => now(),
            'expires_at'         => $this->deliverable_type === self::TYPE_VR
                ? now()->addMonths(3)
                : null,
        ]);

        return true;
    }

    /** 失効させる */
    public function void(): void
    {
        $this->update([
            'deliverable_status' => self::STATUS_VOID,
            'is_active'          => false,
        ]);
    }
}
