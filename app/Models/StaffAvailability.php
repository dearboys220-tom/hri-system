<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffAvailability extends Model
{
    protected $table = 'staff_availability';

    protected $fillable = [
        'staff_user_id',
        'availability_status',
        'active_task_count',
        'max_concurrent_tasks',
        'last_reported_at',
        'absence_until',
        'department_code',
        'skill_tags_json',
    ];

    protected $casts = [
        'last_reported_at'    => 'datetime',
        'absence_until'       => 'date',
        'skill_tags_json'     => 'array',
        'active_task_count'   => 'integer',
        'max_concurrent_tasks'=> 'integer',
    ];

    // ステータス定数
    const STATUS_AVAILABLE  = 'AVAILABLE';
    const STATUS_BUSY       = 'BUSY';
    const STATUS_ON_LEAVE   = 'ON_LEAVE';
    const STATUS_SUSPENDED  = 'SUSPENDED';

    // ---- リレーション ----

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_user_id');
    }

    // ---- ヘルパー ----

    public function isAvailable(): bool
    {
        return $this->availability_status === self::STATUS_AVAILABLE;
    }

    /**
     * 担当者自動検出 — AVAILABLE かつ指定部署の空きスタッフを返す
     * Section 3.29 の優先順位に従う
     */
    public static function findAvailable(string $departmentCode, ?array $requiredSkills = []): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('availability_status', self::STATUS_AVAILABLE)
            ->where('department_code', $departmentCode)
            ->orderBy('active_task_count', 'asc')
            ->orderBy('last_reported_at', 'desc')
            ->get();
    }

    /**
     * タスク数増加（割当時に呼ぶ）
     */
    public function incrementTaskCount(): void
    {
        $this->increment('active_task_count');
        $this->updateStatusByTaskCount();
    }

    /**
     * タスク数減少（完了時に呼ぶ）
     */
    public function decrementTaskCount(): void
    {
        if ($this->active_task_count > 0) {
            $this->decrement('active_task_count');
        }
        $this->updateStatusByTaskCount();
    }

    private function updateStatusByTaskCount(): void
    {
        $this->refresh();
        if ($this->availability_status === self::STATUS_ON_LEAVE ||
            $this->availability_status === self::STATUS_SUSPENDED) {
            return; // 休暇・停止中は変更しない
        }

        if ($this->active_task_count >= $this->max_concurrent_tasks) {
            $this->update(['availability_status' => self::STATUS_BUSY]);
        } else {
            $this->update(['availability_status' => self::STATUS_AVAILABLE]);
        }
    }
}