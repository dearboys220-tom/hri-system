<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_no', 'order_id', 'estimate_id',
        'client_name', 'client_email', 'service_type',
        'subtotal', 'discount_amount', 'final_amount', 'tax_note',
        'payment_terms', 'due_date', 'status',
        'paid_at', 'payment_method', 'payment_notes',
        'created_by_user_id', 'approved_by_user_id',
        'approved_at', 'sent_at',
    ];

    protected $casts = [
        'due_date'    => 'date',
        'paid_at'     => 'datetime',
        'approved_at' => 'datetime',
        'sent_at'     => 'datetime',
    ];

    const STATUS_DRAFT            = 'draft';
    const STATUS_PENDING_APPROVAL = 'pending_approval';
    const STATUS_APPROVED         = 'approved';
    const STATUS_SENT             = 'sent';
    const STATUS_PAID             = 'paid';
    const STATUS_OVERDUE          = 'overdue';
    const STATUS_CANCELLED        = 'cancelled';

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function estimate(): BelongsTo
    {
        return $this->belongsTo(Estimate::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}