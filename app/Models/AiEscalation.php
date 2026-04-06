<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiEscalation extends Model
{
    protected $fillable = [
        'task_assignment_id',
        'escalation_type',
        'escalation_reason',
        'reported_to_user_id',
        'status',
        'escalated_at',
        'resolved_at',
    ];

    protected $casts = [
        'escalated_at' => 'datetime',
        'resolved_at'  => 'datetime',
    ];

    // escalation_type 定数
    const TYPE_DELAY         = 'DELAY';
    const TYPE_NO_REPORT     = 'NO_REPORT';
    const TYPE_NOT_EXECUTED  = 'NOT_EXECUTED';
    const TYPE_INCONSISTENCY = 'INCONSISTENCY';
    const TYPE_REFUSAL       = 'REFUSAL';

    // status 定数
    const STATUS_OPEN         = 'OPEN';
    const STATUS_ACKNOWLEDGED = 'ACKNOWLEDGED';
    const STATUS_RESOLVED     = 'RESOLVED';
    const STATUS_CLOSED       = 'CLOSED';

    // -------------------------------------------------------
    // リレーション
    // -------------------------------------------------------
    public function taskAssignment(): BelongsTo
    {
        return $this->belongsTo(AiTaskAssignment::class, 'task_assignment_id');
    }

    public function reportedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_to_user_id');
    }

    // -------------------------------------------------------
    // スコープ
    // -------------------------------------------------------
    public function scopeOpen($query)
    {
        return $query->where('status', self::STATUS_OPEN);
    }
}
