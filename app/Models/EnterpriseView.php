<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// ============================================================
// EnterpriseView
// ============================================================
class EnterpriseView extends Model
{
    protected $fillable = [
        'case_no',
        'company_user_id',
        'deliverable_id',
        'token_id',
        'viewed_at',
        'ip_address',
        'user_agent',
        'remaining_views_after',
        'is_expired_view',
    ];

    protected $casts = [
        'viewed_at'       => 'datetime',
        'is_expired_view' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(User::class, 'company_user_id');
    }

    public function deliverable(): BelongsTo
    {
        return $this->belongsTo(CaseDeliverable::class, 'deliverable_id');
    }
}
