<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// ============================================================
// AiActivityLog
// ============================================================
class AiActivityLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'log_type',
        'related_type',
        'related_id',
        'triggered_by_user_id',
        'triggered_by_role',
        'model_name',
        'prompt_version',
        'input_summary',
        'output_summary',
        'final_decision',
        'tokens_input',
        'tokens_output',
        'tokens_total',
        'estimated_cost_idr',
        'latency_ms',
        'status',
        'error_message',
        'created_at',
    ];

    protected $casts = [
        'estimated_cost_idr' => 'decimal:2',
        'created_at'         => 'datetime',
    ];

    // log_type 定数
    const TYPE_SCORING           = 'scoring';
    const TYPE_PRIORITY_ANALYSIS = 'priority_analysis';
    const TYPE_CHAT_SUPPORT      = 'chat_support';
    const TYPE_EM_ACTION         = 'em_action';

    // status 定数
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED  = 'failed';
    const STATUS_TIMEOUT = 'timeout';

    public function triggeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by_user_id');
    }

    /** ログ記録ファクトリ */
    public static function record(array $data): self
    {
        return static::create(array_merge([
            'status'     => self::STATUS_SUCCESS,
            'created_at' => now(),
        ], $data));
    }
}
