<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiEmployeeReport extends Model
{
    protected $fillable = [
        'task_assignment_id',
        'employee_user_id',
        'report_body',
        'ai_summary',
        'evidence_attached_flag',
        'inconsistency_flag',
        'reported_at',
    ];

    protected $casts = [
        'evidence_attached_flag' => 'boolean',
        'inconsistency_flag'     => 'boolean',
        'reported_at'            => 'datetime',
    ];

    // -------------------------------------------------------
    // リレーション
    // -------------------------------------------------------
    public function taskAssignment(): BelongsTo
    {
        return $this->belongsTo(AiTaskAssignment::class, 'task_assignment_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_user_id');
    }
}
