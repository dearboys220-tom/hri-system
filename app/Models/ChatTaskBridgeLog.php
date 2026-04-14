<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatTaskBridgeLog extends Model
{
    protected $fillable = [
        'internal_chat_log_id', 'issued_by_user_id',
        'original_message_ja', 'interpreted_instruction',
        'target_division', 'priority_level',
        'task_order_created', 'task_order_id', 'bridge_status',
    ];

    protected $casts = [
        'task_order_created' => 'boolean',
    ];

    public function taskOrder()
    {
        return $this->belongsTo(AiTaskOrder::class, 'task_order_id');
    }
}