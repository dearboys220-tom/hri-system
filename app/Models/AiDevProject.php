<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiDevProject extends Model
{
    protected $fillable = [
        'project_no', 'client_company_id', 'lead_user_id',
        'project_name', 'project_type', 'project_description',
        'project_status', 'ai_progress_summary',
        'contract_amount', 'billing_status',
        'started_at', 'delivery_due_at', 'delivered_at',
    ];

    protected $casts = [
        'contract_amount' => 'decimal:2',
        'started_at'      => 'date',
        'delivery_due_at' => 'date',
        'delivered_at'    => 'date',
    ];

    const STATUS_PROPOSAL   = 'PROPOSAL';
    const STATUS_CONTRACTED = 'CONTRACTED';
    const STATUS_IN_PROGRESS = 'IN_PROGRESS';
    const STATUS_TESTING    = 'TESTING';
    const STATUS_DELIVERED  = 'DELIVERED';
    const STATUS_MAINTENANCE = 'MAINTENANCE';
    const STATUS_COMPLETED  = 'COMPLETED';
    const STATUS_CANCELLED  = 'CANCELLED';

    public function clientCompany(): BelongsTo
    {
        return $this->belongsTo(CompanyProfile::class, 'client_company_id');
    }

    public function leadUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lead_user_id');
    }

    public function isOverdue(): bool
    {
        return $this->delivery_due_at
            && $this->delivery_due_at->isPast()
            && !in_array($this->project_status, [self::STATUS_DELIVERED, self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }
}