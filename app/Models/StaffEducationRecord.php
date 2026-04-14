<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffEducationRecord extends Model
{
    protected $fillable = [
        'staff_user_id',
        'module_code',
        'is_completed',
        'completed_at',
        'quiz_score',
        'attempt_count',
    ];

    protected $casts = [
        'is_completed'  => 'boolean',
        'completed_at'  => 'datetime',
        'quiz_score'    => 'integer',
        'attempt_count' => 'integer',
    ];

    // ─── リレーション ───
    public function staffUser()
    {
        return $this->belongsTo(User::class, 'staff_user_id');
    }

    // ─── スコープ ───
    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('staff_user_id', $userId);
    }

    // ─── スタティックヘルパー ───

    /**
     * 特定ユーザーが特定モジュールを完了しているか
     */
    public static function isCompleted(int $userId, string $moduleCode): bool
    {
        return static::where('staff_user_id', $userId)
            ->where('module_code', $moduleCode)
            ->where('is_completed', true)
            ->exists();
    }

    /**
     * ユーザーの完了済みモジュールコード一覧を返す
     */
    public static function completedCodes(int $userId): array
    {
        return static::where('staff_user_id', $userId)
            ->where('is_completed', true)
            ->pluck('module_code')
            ->toArray();
    }
}