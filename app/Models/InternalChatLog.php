<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InternalChatLog extends Model
{
    protected $fillable = [
        'user_id', 'role_type', 'session_id',
        'message_role', 'message_content',
        'message_content_ja', 'message_content_ko', 'message_content_id',
        'source_language', 'is_task_instruction', 'task_order_id',
        'tokens_used', 'model_name',
    ];

    protected $casts = [
        'is_task_instruction' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function taskOrder()
    {
        return $this->belongsTo(AiTaskOrder::class, 'task_order_id');
    }
}