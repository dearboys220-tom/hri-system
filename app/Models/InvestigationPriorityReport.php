<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// ============================================================
// InvestigationPriorityReport
// ============================================================
class InvestigationPriorityReport extends Model
{
    protected $fillable = [
        'case_no',
        'certification_request_id',
        'ai_model',
        'prompt_version',
        'priority_high_json',
        'priority_medium_json',
        'priority_low_json',
        'conduct_contacts_json',
        'risk_flags_json',
        'ai_analysis_summary',
        'generated_at',
    ];

    protected $casts = [
        'priority_high_json'   => 'array',
        'priority_medium_json' => 'array',
        'priority_low_json'    => 'array',
        'conduct_contacts_json'=> 'array',
        'risk_flags_json'      => 'array',
        'generated_at'         => 'datetime',
    ];

    public function certificationRequest(): BelongsTo
    {
        return $this->belongsTo(CertificationRequest::class);
    }
}
