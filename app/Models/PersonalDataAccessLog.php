<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonalDataAccessLog extends Model
{
    public $timestamps = false; // accessed_at のみ管理

    protected $fillable = [
        'case_no',
        'accessor_user_id',
        'accessor_role',
        'target_user_id',
        'target_member_id',
        'target_table',
        'target_record_id',
        'data_type',
        'fields_accessed_json',
        'action',
        'access_reason',
        'related_id',
        'ip_address',
        'accessed_at',
    ];

    protected $casts = [
        'fields_accessed_json' => 'array',
        'accessed_at'          => 'datetime',
    ];

    // action 定数
    const ACTION_VIEW     = 'view';
    const ACTION_DOWNLOAD = 'download';
    const ACTION_EXPORT   = 'export';
    const ACTION_AI_SEND  = 'ai_send';

    // access_reason 定数
    const REASON_INVESTIGATION    = 'investigation';
    const REASON_REVIEW           = 'review';
    const REASON_COMPANY_PURCHASE = 'company_purchase';
    const REASON_ADMIN_CHECK      = 'admin_check';
    const REASON_SUPER_ADMIN      = 'super_admin';

    // -------------------------------------------------------
    // リレーション
    // -------------------------------------------------------
    public function accessor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accessor_user_id');
    }

    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    // -------------------------------------------------------
    // ファクトリメソッド（ログ記録用）
    // -------------------------------------------------------

    /**
     * 個人データアクセスを記録する
     *
     * @param int    $accessorUserId  アクセスしたユーザーID
     * @param string $accessorRole    アクセス時のロール
     * @param int    $targetUserId    対象ユーザーID
     * @param string $dataType        データ種別
     * @param string $action          アクション
     * @param string $reason          アクセス理由
     * @param array  $options         追加オプション
     */
    public static function record(
        int    $accessorUserId,
        string $accessorRole,
        int    $targetUserId,
        string $dataType,
        string $action,
        string $reason,
        array  $options = []
    ): self {
        return static::create([
            'accessor_user_id'   => $accessorUserId,
            'accessor_role'      => $accessorRole,
            'target_user_id'     => $targetUserId,
            'data_type'          => $dataType,
            'action'             => $action,
            'access_reason'      => $reason,
            'case_no'            => $options['case_no'] ?? null,
            'target_member_id'   => $options['target_member_id'] ?? null,
            'target_table'       => $options['target_table'] ?? null,
            'target_record_id'   => $options['target_record_id'] ?? null,
            'fields_accessed_json' => $options['fields'] ?? null,
            'related_id'         => $options['related_id'] ?? null,
            'ip_address'         => request()->ip(),
            'accessed_at'        => now(),
        ]);
    }
}
