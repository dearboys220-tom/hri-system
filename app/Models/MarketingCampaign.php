<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingCampaign extends Model
{
    protected $fillable = [
        'campaign_no', 'assigned_user_id',
        'campaign_name', 'campaign_type', 'target_description',
        'campaign_status', 'ai_summary',
        'budget', 'started_at', 'ended_at', 'result_report',
    ];

    protected $casts = [
        'budget'     => 'decimal:2',
        'started_at' => 'date',
        'ended_at'   => 'date',
    ];

    const STATUS_PLANNING   = 'PLANNING';
    const STATUS_ACTIVE     = 'ACTIVE';
    const STATUS_ON_HOLD    = 'ON_HOLD';
    const STATUS_COMPLETED  = 'COMPLETED';
    const STATUS_CANCELLED  = 'CANCELLED';

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }
}