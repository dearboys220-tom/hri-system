<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiChatLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'case_no',
        'user_id',
        'role_type',
        'session_id',
        'related_type',
        'related_id',
        'message_role',
        'message_content',
        'contains_pii_flag',
        'masked_content',
        'blocked_reason',
        'tokens_used',
        'model_name',
        'created_at',
    ];

    protected $casts = [
        'contains_pii_flag' => 'boolean',
        'created_at'        => 'datetime',
    ];

    // message_role 定数
    const ROLE_USER      = 'user';
    const ROLE_ASSISTANT = 'assistant';

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

    /** セッション単位で取得 */
    public function scopeSession($query, string $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    /** 90日以内のログのみ（保存期間制限） */
    public function scopeWithinRetention($query)
    {
        return $query->where('created_at', '>=', now()->subDays(90));
    }

    // -------------------------------------------------------
    // ヘルパー
    // -------------------------------------------------------

    /**
     * PII（個人識別情報）のマスキング処理
     * NIK（16桁数字）・電話番号（+62始まり）をマスク
     */
    public static function maskPii(string $content): array
    {
        $masked  = $content;
        $hasPii  = false;

        // NIK（16桁数字）
        if (preg_match('/\b\d{16}\b/', $masked)) {
            $masked = preg_replace('/\b\d{16}\b/', '[NIK-MASKED]', $masked);
            $hasPii = true;
        }

        // インドネシア電話番号（+62 or 08始まり）
        if (preg_match('/(\+62|08)\d{8,12}/', $masked)) {
            $masked = preg_replace('/(\+62|08)\d{8,12}/', '[PHONE-MASKED]', $masked);
            $hasPii = true;
        }

        // メールアドレス
        if (preg_match('/[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}/', $masked)) {
            $masked = preg_replace(
                '/[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}/',
                '[EMAIL-MASKED]',
                $masked
            );
            $hasPii = true;
        }

        return ['content' => $masked, 'has_pii' => $hasPii];
    }
}
