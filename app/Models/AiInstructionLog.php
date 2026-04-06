<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiInstructionLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'order_no',
        'task_assignment_id',
        'actor_type',
        'actor_user_id',
        'action_type',
        'action_summary',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // actor_type 定数
    const ACTOR_HUMAN  = 'human';
    const ACTOR_AI     = 'ai';
    const ACTOR_SYSTEM = 'system';

    // action_type 定数
    const ACTION_ORDER_CREATED         = 'ORDER_CREATED';
    const ACTION_ORDER_APPROVED        = 'ORDER_APPROVED';
    const ACTION_AI_ASSIGNED           = 'AI_ASSIGNED';
    const ACTION_EMPLOYEE_ACKNOWLEDGED = 'EMPLOYEE_ACKNOWLEDGED';
    const ACTION_EMPLOYEE_REPORTED     = 'EMPLOYEE_REPORTED';
    const ACTION_AI_SUMMARIZED         = 'AI_SUMMARIZED';
    const ACTION_MANAGER_VERIFIED      = 'MANAGER_VERIFIED';
    const ACTION_ESCALATED             = 'ESCALATED';
    const ACTION_RESOLVED              = 'RESOLVED';
    const ACTION_CLOSED                = 'CLOSED';

    // -------------------------------------------------------
    // リレーション
    // -------------------------------------------------------
    public function taskAssignment(): BelongsTo
    {
        return $this->belongsTo(AiTaskAssignment::class, 'task_assignment_id');
    }

    public function actorUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    // -------------------------------------------------------
    // ファクトリ（ログを1行で記録）
    // -------------------------------------------------------
    public static function record(
        string  $orderNo,
        string  $actorType,
        string  $actionType,
        ?int    $taskAssignmentId = null,
        ?int    $actorUserId      = null,
        ?string $summary          = null
    ): self {
        return static::create([
            'order_no'           => $orderNo,
            'task_assignment_id' => $taskAssignmentId,
            'actor_type'         => $actorType,
            'actor_user_id'      => $actorUserId ?? auth()->id(),
            'action_type'        => $actionType,
            'action_summary'     => $summary,
            'created_at'         => now(),
        ]);
    }
}
