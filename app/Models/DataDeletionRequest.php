<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataDeletionRequest extends Model
{
    protected $fillable = [
        'user_id',
        'request_reason',
        'scope',
        'status',
        'reject_reason',
        'processed_by',
        'requested_at',
        'completed_at',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // status 定数
    const STATUS_PENDING      = 'pending';
    const STATUS_UNDER_REVIEW = 'under_review';
    const STATUS_APPROVED     = 'approved';
    const STATUS_COMPLETED    = 'completed';
    const STATUS_REJECTED     = 'rejected';

    // scope 定数
    const SCOPE_ALL_DATA          = 'all_data';
    const SCOPE_PROFILE_ONLY      = 'profile_only';
    const SCOPE_CERTIFICATION_DATA = 'certification_data';

    // -------------------------------------------------------
    // リレーション
    // -------------------------------------------------------
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    // -------------------------------------------------------
    // スコープ
    // -------------------------------------------------------
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeUnresolved($query)
    {
        return $query->whereIn('status', [
            self::STATUS_PENDING,
            self::STATUS_UNDER_REVIEW,
            self::STATUS_APPROVED,
        ]);
    }
}
