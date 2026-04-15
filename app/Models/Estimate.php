<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Estimate extends Model
{
    protected $fillable = [
        'estimate_no', 'title', 'client_name', 'client_email', 'service_type',
        'scope_included', 'scope_excluded', 'special_notes',
        'subtotal', 'discount_exists', 'discount_amount', 'discount_reason',
        'final_amount', 'tax_note', 'payment_terms',
        'validity_days', 'valid_until', 'contract_required', 'nda_required',
        'status',
        'ai_estimate_body', 'ai_cover_email_draft', 'ai_approval_note',
        'ai_risk_flags', 'ai_missing_items', 'ai_generated_at',
        'created_by_user_id', 'approved_by_user_id',
        'approved_at', 'sent_at', 'accepted_at', 'expired_at',
    ];

    protected $casts = [
        'discount_exists'    => 'boolean',
        'contract_required'  => 'boolean',
        'nda_required'       => 'boolean',
        'ai_risk_flags'      => 'array',
        'ai_missing_items'   => 'array',
        'ai_generated_at'    => 'datetime',
        'valid_until'        => 'date',
        'approved_at'        => 'datetime',
        'sent_at'            => 'datetime',
        'accepted_at'        => 'datetime',
        'expired_at'         => 'datetime',
    ];

    // ステータス定数（Section 29.4）
    const STATUS_DRAFT            = 'draft';
    const STATUS_PENDING_APPROVAL = 'pending_approval';
    const STATUS_APPROVED         = 'approved';
    const STATUS_SENT             = 'sent';
    const STATUS_ACCEPTED         = 'accepted';
    const STATUS_REVISED          = 'revised';
    const STATUS_EXPIRED          = 'expired';

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function order(): HasOne
    {
        return $this->hasOne(Order::class);
    }
}