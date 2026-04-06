<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiManagerVerification extends Model
{
    protected $fillable = [
        'task_assignment_id',
        'manager_user_id',
        'verification_result',
        'verification_comment',
        'verified_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    // verification_result 定数
    const RESULT_EXECUTED           = 'EXECUTED';
    const RESULT_PARTIALLY_EXECUTED = 'PARTIALLY_EXECUTED';
    const RESULT_NOT_EXECUTED       = 'NOT_EXECUTED';
    const RESULT_UNCONFIRMED        = 'UNCONFIRMED';

    // -------------------------------------------------------
    // ⚠️ AIによる verification_result の自動セット禁止
    //    現実世界での実行確認は必ず人間管理者が行うこと
    // -------------------------------------------------------

    // -------------------------------------------------------
    // リレーション
    // -------------------------------------------------------
    public function taskAssignment(): BelongsTo
    {
        return $this->belongsTo(AiTaskAssignment::class, 'task_assignment_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_user_id');
    }
}
