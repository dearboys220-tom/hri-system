<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskCompletionReport extends Model
{
    protected $fillable = [
        'task_order_id',
        'report_no',
        'generated_by_ai',
        'report_title',
        'execution_summary',
        'result_summary',
        'completion_rate',
        'on_time_flag',
        'assignee_evaluations_json',
        'issues_identified',
        'recommendations',
        'reported_to_user_id',
        'report_status',
        'delivered_at',
        'acknowledged_at',
    ];

    protected $casts = [
        'generated_by_ai'           => 'boolean',
        'on_time_flag'              => 'boolean',
        'completion_rate'           => 'decimal:2',
        'assignee_evaluations_json' => 'array',
        'delivered_at'              => 'datetime',
        'acknowledged_at'           => 'datetime',
    ];

    // ステータス定数
    const STATUS_DRAFT       = 'DRAFT';
    const STATUS_DELIVERED   = 'DELIVERED';
    const STATUS_ACKNOWLEDGED = 'ACKNOWLEDGED';

    // ---- リレーション ----

    public function taskOrder(): BelongsTo
    {
        return $this->belongsTo(AiTaskOrder::class, 'task_order_id');
    }

    public function reportedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_to_user_id');
    }
}