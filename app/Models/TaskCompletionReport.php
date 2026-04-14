<?php

namespace App\Models;

use App\Services\NumberingService;
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
    const STATUS_DRAFT        = 'DRAFT';
    const STATUS_DELIVERED    = 'DELIVERED';
    const STATUS_ACKNOWLEDGED = 'ACKNOWLEDGED';

    // -------------------------------------------------------
    // 採番（Section 28・32 準拠）
    // creating 時に report_no が空であれば自動で HRI-RPT-ADMIN-YYYY-NNNN を発行
    // created ではなく creating（保存前）に発行する点に注意
    // -------------------------------------------------------
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (TaskCompletionReport $report) {
            if (empty($report->report_no)) {
                $report->report_no = app(NumberingService::class)->issueReportNo('ADMIN');
            }
        });
    }

    // -------------------------------------------------------
    // リレーション
    // -------------------------------------------------------
    public function taskOrder(): BelongsTo
    {
        return $this->belongsTo(AiTaskOrder::class, 'task_order_id');
    }

    public function reportedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_to_user_id');
    }

    // -------------------------------------------------------
    // ヘルパー
    // -------------------------------------------------------

    /** レポートが配信済みか */
    public function isDelivered(): bool
    {
        return $this->report_status === self::STATUS_DELIVERED
            || $this->report_status === self::STATUS_ACKNOWLEDGED;
    }

    /** 命令者が確認済みか */
    public function isAcknowledged(): bool
    {
        return $this->report_status === self::STATUS_ACKNOWLEDGED;
    }

    /** 期限内に完了したか（表示用） */
    public function onTimeLabel(): string
    {
        return $this->on_time_flag ? 'Tepat Waktu' : 'Terlambat';
    }
}