<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsentRecord extends Model
{
    protected $fillable = [
        'user_id',
        'case_no',
        'consent_type',
        'consent_version',
        'consent_hash',
        'consented',
        'consented_at',
        'withdrawn_at',
        'source_channel',
        'token_id',
        'expires_at',
        'remaining_views',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'consented'     => 'boolean',
        'consented_at'  => 'datetime',
        'withdrawn_at'  => 'datetime',
        'expires_at'    => 'datetime',
    ];

    // -------------------------------------------------------
    // consent_type 定数
    // -------------------------------------------------------
    const TYPE_ACCOUNT_TERMS                   = 'ACCOUNT_TERMS';
    const TYPE_ACCOUNT_PRIVACY                 = 'ACCOUNT_PRIVACY';
    const TYPE_AI_PROCESSING                   = 'ai_processing';
    const TYPE_DATA_TRANSFER_ABROAD            = 'data_transfer_abroad';
    const TYPE_VERIFIED_RESUME_THIRD_PARTY_VIEW = 'VERIFIED_RESUME_THIRD_PARTY_VIEW';
    const TYPE_COOKIE_ANALYTICS                = 'COOKIE_ANALYTICS';
    const TYPE_COOKIE_MARKETING                = 'COOKIE_MARKETING';
    const TYPE_MARKETING                       = 'marketing';

    // -------------------------------------------------------
    // リレーション
    // -------------------------------------------------------
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // -------------------------------------------------------
    // スコープ
    // -------------------------------------------------------

    /** 有効な同意のみ（撤回なし・期限内） */
    public function scopeActive($query)
    {
        return $query->where('consented', true)
                     ->whereNull('withdrawn_at')
                     ->where(function ($q) {
                         $q->whereNull('expires_at')
                           ->orWhere('expires_at', '>', now());
                     });
    }

    /** 同意種別で絞り込み */
    public function scopeOfType($query, string $type)
    {
        return $query->where('consent_type', $type);
    }

    // -------------------------------------------------------
    // ヘルパー
    // -------------------------------------------------------

    /** 有効な同意かどうか */
    public function isActive(): bool
    {
        if (! $this->consented || $this->withdrawn_at !== null) {
            return false;
        }
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }
        return true;
    }

    /**
     * 指定ユーザーが指定種別の有効な同意を持つか確認
     */
    public static function hasActiveConsent(int $userId, string $consentType): bool
    {
        return static::where('user_id', $userId)
                     ->ofType($consentType)
                     ->active()
                     ->exists();
    }
}
