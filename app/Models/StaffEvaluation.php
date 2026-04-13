<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffEvaluation extends Model
{
    protected $fillable = [
        'staff_user_id',
        'task_order_id',
        'evaluation_period_from',
        'evaluation_period_to',
        'evaluation_type',
        'ai_performance_band',
        'ai_score',
        'ai_evaluation_summary',
        'ai_evaluation_detail',
        'ai_recommended_action',
        'warning_draft_triggered',
        'human_final_band',
        'human_override_reason',
        'approved_by_user_id',
        'approved_at',
        'salary_calculation_id',
    ];

    protected $casts = [
        'evaluation_period_from'  => 'date',
        'evaluation_period_to'    => 'date',
        'ai_score'                => 'decimal:2',
        'ai_evaluation_detail'    => 'array',
        'warning_draft_triggered' => 'boolean',
        'approved_at'             => 'datetime',
    ];

    // バンド定数
    const BAND_GOOD    = 'GOOD';
    const BAND_FAIR    = 'FAIR';
    const BAND_WARNING = 'WARNING';

    // 評価タイプ定数
    const TYPE_TASK_BASED = 'TASK_BASED';
    const TYPE_MONTHLY    = 'MONTHLY';
    const TYPE_QUARTERLY  = 'QUARTERLY';

    // 推奨アクション定数
    const ACTION_COMMEND       = 'COMMEND';
    const ACTION_MONITOR       = 'MONITOR';
    const ACTION_WARNING_DRAFT = 'WARNING_DRAFT';

    // ---- リレーション ----

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_user_id');
    }

    public function taskOrder(): BelongsTo
    {
        return $this->belongsTo(AiTaskOrder::class, 'task_order_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function salaryCalculation(): BelongsTo
    {
        return $this->belongsTo(SalaryCalculation::class);
    }

    // ---- ヘルパー ----

    public function isConfirmed(): bool
    {
        return $this->human_final_band !== null;
    }
}