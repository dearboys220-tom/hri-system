<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffActivityLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'case_no',
        'user_id',
        'role_type',
        'action',
        'target_type',
        'target_id',
        'description',
        'before_value',
        'after_value',
        'export_reason',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'before_value' => 'array',
        'after_value'  => 'array',
        'created_at'   => 'datetime',
    ];

    // -------------------------------------------------------
    // リレーション
    // -------------------------------------------------------
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // -------------------------------------------------------
    // ファクトリ（操作ログを簡単に記録）
    // -------------------------------------------------------
    public static function record(
        string $action,
        string $targetType,
        ?int   $targetId = null,
        array  $options  = []
    ): self {
        $user = auth()->user();

        return static::create([
            'case_no'      => $options['case_no'] ?? null,
            'user_id'      => $user?->id,
            'role_type'    => $user?->role_type,
            'action'       => $action,
            'target_type'  => $targetType,
            'target_id'    => $targetId,
            'description'  => $options['description'] ?? null,
            'before_value' => $options['before'] ?? null,
            'after_value'  => $options['after'] ?? null,
            'export_reason'=> $options['export_reason'] ?? null,
            'ip_address'   => request()->ip(),
            'user_agent'   => request()->userAgent(),
            'created_at'   => now(),
        ]);
    }
}
