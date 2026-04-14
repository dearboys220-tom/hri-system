<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StrategyCase extends Model
{
    protected $fillable = [
        'case_no', 'client_company_id', 'assigned_user_id',
        'case_type', 'case_title', 'case_description',
        'requires_registered_lawyer', 'lawyer_contract_id',
        'case_status', 'risk_level', 'ai_risk_summary',
        'human_review_required', 'resolution_summary',
        'billing_status', 'fee_amount', 'started_at', 'resolved_at',
    ];

    protected $casts = [
        'requires_registered_lawyer' => 'boolean',
        'human_review_required'      => 'boolean',
        'fee_amount'                 => 'decimal:2',
        'started_at'                 => 'date',
        'resolved_at'                => 'date',
    ];

    // ステータス定数
    const STATUS_OPEN           = 'OPEN';
    const STATUS_IN_PROGRESS    = 'IN_PROGRESS';
    const STATUS_PENDING_LAWYER = 'PENDING_LAWYER';
    const STATUS_RESOLVED       = 'RESOLVED';
    const STATUS_CLOSED         = 'CLOSED';
    const STATUS_ESCALATED      = 'ESCALATED';

    const RISK_HIGH   = 'HIGH';
    const RISK_MEDIUM = 'MEDIUM';
    const RISK_LOW    = 'LOW';

    public function clientCompany(): BelongsTo
    {
        return $this->belongsTo(CompanyProfile::class, 'client_company_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function isHighRisk(): bool
    {
        return $this->risk_level === self::RISK_HIGH;
    }
}