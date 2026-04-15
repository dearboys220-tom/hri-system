<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonthlyAccountingReport extends Model
{
    protected $fillable = [
        'report_month', 'period_from', 'period_to', 'status',
        'total_revenue', 'total_pending', 'paid_invoice_count',
        'pending_invoice_count', 'included_invoice_ids',
        'expense_items', 'total_expenses',
        'ai_summary', 'ai_anomaly_notes', 'ai_draft_cover_letter',
        'ai_checklist', 'ai_risk_flags', 'ai_generated_at',
        'created_by_user_id', 'approved_by_user_id', 'approved_at',
        'sent_to_email', 'sent_at', 'acknowledged_at', 'notes',
    ];

    protected $casts = [
        'period_from'          => 'date',
        'period_to'            => 'date',
        'included_invoice_ids' => 'array',
        'expense_items'        => 'array',
        'ai_risk_flags'        => 'array',
        'ai_generated_at'      => 'datetime',
        'approved_at'          => 'datetime',
        'sent_at'              => 'datetime',
        'acknowledged_at'      => 'datetime',
    ];

    const STATUS_DRAFT            = 'draft';
    const STATUS_AI_ORGANIZED     = 'ai_organized';
    const STATUS_PENDING_APPROVAL = 'pending_approval';
    const STATUS_APPROVED         = 'approved';
    const STATUS_SENT             = 'sent';
    const STATUS_ACKNOWLEDGED     = 'acknowledged';

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }
}