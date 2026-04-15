<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = [
        'order_no', 'estimate_id', 'client_name', 'client_email',
        'service_type', 'final_amount', 'payment_terms', 'notes',
        'status', 'created_by_user_id', 'approved_by_user_id',
        'approved_at', 'completed_at',
    ];

    protected $casts = [
        'approved_at'  => 'datetime',
        'completed_at' => 'datetime',
    ];

    const STATUS_CONFIRMED   = 'confirmed';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED   = 'completed';
    const STATUS_CANCELLED   = 'cancelled';

    public function estimate(): BelongsTo
    {
        return $this->belongsTo(Estimate::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }
}