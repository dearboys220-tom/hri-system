<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiTaskOrder extends Model
{
    protected $fillable = [
        'order_no',
        'issued_by_user_id',
        'approver_user_id',
        'instruction_title',
        'instruction_body',
        'target_division',
        'priority_level',
        'due_at',
        'approval_status',
        'ai_processing_status',
        'visibility_scope',
    ];

    protected $casts = [
        'due_at' => 'datetime',
    ];

    // approval_status 定数
    const APPROVAL_DRAFT            = 'DRAFT';
    const APPROVAL_PENDING_APPROVAL = 'PENDING_APPROVAL';
    const APPROVAL_APPROVED         = 'APPROVED';
    const APPROVAL_REJECTED         = 'REJECTED';
    const APPROVAL_CANCELLED        = 'CANCELLED';

    // ai_processing_status 定数
    const AI_NOT_STARTED = 'NOT_STARTED';
    const AI_ASSIGNED    = 'ASSIGNED';
    const AI_IN_PROGRESS = 'IN_PROGRESS';
    const AI_COMPLETED   = 'COMPLETED';
    const AI_ESCALATED   = 'ESCALATED';

    // -------------------------------------------------------
    // リレーション
    // -------------------------------------------------------
    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by_user_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_user_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(AiTaskAssignment::class, 'task_order_id');
    }

    public function instructionLogs(): HasMany
    {
        return $this->hasMany(AiInstructionLog::class, 'order_no', 'order_no');
    }

    // -------------------------------------------------------
    // スコープ
    // -------------------------------------------------------
    public function scopeApproved($query)
    {
        return $query->where('approval_status', self::APPROVAL_APPROVED);
    }

    // -------------------------------------------------------
    // ヘルパー
    // -------------------------------------------------------

    /** AIが処理可能かチェック（APPROVED のみ処理可） */
    public function isProcessable(): bool
    {
        return $this->approval_status === self::APPROVAL_APPROVED;
    }
}
