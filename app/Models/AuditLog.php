<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'case_no',
        'user_id',
        'actor_type',
        'action_type',
        'old_values_json',
        'new_values_json',
        'ip_address',
        'user_agent',
        'session_id',
        'request_uri',
        'request_method',
        'prompt_version',
        'serial_no',
        'created_at',
    ];

    protected $casts = [
        'old_values_json' => 'array',
        'new_values_json' => 'array',
        'created_at'      => 'datetime',
    ];

    // actor_type 定数
    const ACTOR_HUMAN  = 'human';
    const ACTOR_AI     = 'ai';
    const ACTOR_SYSTEM = 'system';

    // action_type 定数（30種）
    const ACTION_SIGNUP                  = 'SIGNUP';
    const ACTION_CONSENT_GRANTED         = 'CONSENT_GRANTED';
    const ACTION_CONSENT_WITHDRAWN       = 'CONSENT_WITHDRAWN';
    const ACTION_CERT_REQUEST_CREATED    = 'CERT_REQUEST_CREATED';
    const ACTION_INVESTIGATION_ASSIGNED  = 'INVESTIGATION_ASSIGNED';
    const ACTION_INVESTIGATION_CREATED   = 'INVESTIGATION_CREATED';
    const ACTION_INVESTIGATION_UPDATED   = 'INVESTIGATION_UPDATED';
    const ACTION_POLICY_VIOLATION_FLAGGED = 'POLICY_VIOLATION_FLAGGED';
    const ACTION_AI_REVIEW_RUN           = 'AI_REVIEW_RUN';
    const ACTION_AI_PROPOSED             = 'AI_PROPOSED';
    const ACTION_HUMAN_OVERRIDE          = 'HUMAN_OVERRIDE';
    const ACTION_RETURN_CREATED          = 'RETURN_CREATED';
    const ACTION_RETURN_RESOLVED         = 'RETURN_RESOLVED';
    const ACTION_HUMAN_REVIEW_ASSIGNED   = 'HUMAN_REVIEW_ASSIGNED';
    const ACTION_APPROVED                = 'APPROVED';
    const ACTION_CONDITIONAL_APPROVED    = 'CONDITIONAL_APPROVED';
    const ACTION_REJECTED                = 'REJECTED';
    const ACTION_VR_PENDING              = 'VR_PENDING';
    const ACTION_IR_PENDING              = 'IR_PENDING';
    const ACTION_RN_ISSUED               = 'RN_ISSUED';
    const ACTION_VR_ISSUED               = 'VR_ISSUED';
    const ACTION_IR_ISSUED               = 'IR_ISSUED';
    const ACTION_DELIVERABLE_VOIDED      = 'DELIVERABLE_VOIDED';
    const ACTION_DELIVERABLE_EXPIRED     = 'DELIVERABLE_EXPIRED';
    const ACTION_COMPANY_VIEWED          = 'COMPANY_VIEWED';
    const ACTION_APPLICANT_VIEWED        = 'APPLICANT_VIEWED';
    const ACTION_EXPORT_REQUESTED        = 'EXPORT_REQUESTED';
    const ACTION_DELETE_REQUESTED        = 'DELETE_REQUESTED';
    const ACTION_ACCOUNT_SUSPENDED       = 'ACCOUNT_SUSPENDED';
    const ACTION_SUPER_ADMIN_ACCESS      = 'SUPER_ADMIN_ACCESS';

    // -------------------------------------------------------
    // リレーション
    // -------------------------------------------------------
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // -------------------------------------------------------
    // ★ ファクトリ（監査ログを簡単に記録）
    // -------------------------------------------------------

    /**
     * 人間操作の監査ログを記録
     */
    public static function recordHuman(
        string  $actionType,
        ?string $caseNo  = null,
        array   $options = []
    ): self {
        return static::create([
            'case_no'        => $caseNo,
            'user_id'        => auth()->id(),
            'actor_type'     => self::ACTOR_HUMAN,
            'action_type'    => $actionType,
            'old_values_json'=> $options['old'] ?? null,
            'new_values_json'=> $options['new'] ?? null,
            'serial_no'      => $options['serial_no'] ?? null,
            'ip_address'     => request()->ip(),
            'user_agent'     => request()->userAgent(),
            'session_id'     => session()->getId(),
            'request_uri'    => request()->getRequestUri(),
            'request_method' => request()->method(),
            'created_at'     => now(),
        ]);
    }

    /**
     * AI処理の監査ログを記録
     */
    public static function recordAi(
        string  $actionType,
        ?string $caseNo        = null,
        ?string $promptVersion = null,
        array   $options       = []
    ): self {
        return static::create([
            'case_no'        => $caseNo,
            'user_id'        => null,
            'actor_type'     => self::ACTOR_AI,
            'action_type'    => $actionType,
            'new_values_json'=> $options['new'] ?? null,
            'prompt_version' => $promptVersion,
            'serial_no'      => $options['serial_no'] ?? null,
            'created_at'     => now(),
        ]);
    }

    /**
     * システム処理の監査ログを記録
     */
    public static function recordSystem(
        string  $actionType,
        ?string $caseNo  = null,
        array   $options = []
    ): self {
        return static::create([
            'case_no'        => $caseNo,
            'user_id'        => null,
            'actor_type'     => self::ACTOR_SYSTEM,
            'action_type'    => $actionType,
            'new_values_json'=> $options['new'] ?? null,
            'serial_no'      => $options['serial_no'] ?? null,
            'created_at'     => now(),
        ]);
    }
}
