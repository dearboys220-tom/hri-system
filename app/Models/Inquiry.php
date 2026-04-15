<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Inquiry extends Model
{
    protected $fillable = [
        'inquiry_no',
        'user_id',
        'user_type',
        'subject',
        'body',
        'status',
        'sla_deadline',
        'sla_breached',
        'ai_category',
        'ai_priority',
        'ai_can_answer_immediately',
        'ai_answer_prohibited',
        'ai_identity_check_required',
        'ai_requires_supervisor_review',
        'ai_requires_legal_review',
        'ai_requires_pdp_review',
        'ai_should_escalate',
        'ai_reason_summary',
        'ai_recommended_next_action',
        'ai_draft_reply_direction',
        'ai_risk_flags',
        'ai_classified_at',
        'human_reply',
        'replied_by_user_id',
        'replied_at',
        'closed_at',
    ];

    protected $casts = [
        'sla_deadline'                  => 'datetime',
        'sla_breached'                  => 'boolean',
        'ai_can_answer_immediately'     => 'boolean',
        'ai_answer_prohibited'          => 'boolean',
        'ai_identity_check_required'    => 'boolean',
        'ai_requires_supervisor_review' => 'boolean',
        'ai_requires_legal_review'      => 'boolean',
        'ai_requires_pdp_review'        => 'boolean',
        'ai_should_escalate'            => 'boolean',
        'ai_risk_flags'                 => 'array',
        'ai_classified_at'              => 'datetime',
        'replied_at'                    => 'datetime',
        'closed_at'                     => 'datetime',
    ];

    // ステータス定数（Section 29.3）
    const STATUS_RECEIVED   = 'received';
    const STATUS_CLASSIFIED = 'classified';
    const STATUS_ANSWERED   = 'answered';
    const STATUS_ESCALATED  = 'escalated';
    const STATUS_CLOSED     = 'closed';

    // ── リレーション
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function repliedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'replied_by_user_id');
    }

    // ── SLA計算（Section 30.3）
    public static function calcSlaDeadline(string $userType, string $category = ''): Carbon
    {
        $now = Carbon::now('Asia/Jakarta');

        // 苦情・異議は当日中
        if (in_array($category, ['complaint', 'objection'])) {
            return $now->endOfDay();
        }

        // 企業会員: 1営業日
        if ($userType === 'company') {
            return self::addBusinessDays($now, 1);
        }

        // 一般会員: 2営業日
        return self::addBusinessDays($now, 2);
    }

    private static function addBusinessDays(Carbon $date, int $days): Carbon
    {
        $added = 0;
        $d = $date->copy();
        while ($added < $days) {
            $d->addDay();
            if ($d->isWeekday()) {
                $added++;
            }
        }
        return $d;
    }

    // ── SLA超過チェック
    public function isSlaBreached(): bool
    {
        return $this->sla_deadline && now()->gt($this->sla_deadline)
            && !in_array($this->status, [self::STATUS_ANSWERED, self::STATUS_CLOSED]);
    }
}