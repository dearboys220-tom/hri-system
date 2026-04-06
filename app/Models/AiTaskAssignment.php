<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiTaskAssignment extends Model
{
    protected $fillable = [
        'order_no',
        'task_order_id',
        'employee_user_id',
        'assigned_by_ai_at',
        'assignment_summary',
        'task_status',
        'started_at',
        'due_at',
        'completed_at',
        'delay_flag',
    ];

    protected $casts = [
        'assigned_by_ai_at' => 'datetime',
        'started_at'        => 'datetime',
        'due_at'            => 'datetime',
        'completed_at'      => 'datetime',
        'delay_flag'        => 'boolean',
    ];

    // task_status 定数
    const STATUS_ASSIGNED     = 'ASSIGNED';
    const STATUS_ACKNOWLEDGED = 'ACKNOWLEDGED';
    const STATUS_IN_PROGRESS  = 'IN_PROGRESS';
    const STATUS_COMPLETED    = 'COMPLETED';
    const STATUS_DELAYED      = 'DELAYED';
    const STATUS_FAILED       = 'FAILED';
    const STATUS_ESCALATED    = 'ESCALATED';

    // -------------------------------------------------------
    // リレーション
    // -------------------------------------------------------
    public function taskOrder(): BelongsTo
    {
        return $this->belongsTo(AiTaskOrder::class, 'task_order_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_user_id');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(AiEmployeeReport::class, 'task_assignment_id');
    }

    public function verifications(): HasMany
    {
        return $this->hasMany(AiManagerVerification::class, 'task_assignment_id');
    }

    public function escalations(): HasMany
    {
        return $this->hasMany(AiEscalation::class, 'task_assignment_id');
    }

    public function instructionLogs(): HasMany
    {
        return $this->hasMany(AiInstructionLog::class, 'task_assignment_id');
    }

    // -------------------------------------------------------
    // ヘルパー
    // -------------------------------------------------------

    /** 期限超過チェック・delay_flag を自動セット */
    public function checkAndSetDelayFlag(): bool
    {
        if (
            $this->due_at &&
            $this->due_at->isPast() &&
            ! in_array($this->task_status, [self::STATUS_COMPLETED, self::STATUS_FAILED])
        ) {
            $this->update([
                'delay_flag'  => true,
                'task_status' => self::STATUS_DELAYED,
            ]);
            return true;
        }
        return false;
    }
}
