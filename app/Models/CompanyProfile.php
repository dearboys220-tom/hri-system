<?php

namespace App\Models;

use App\Services\NumberingService;
use Illuminate\Database\Eloquent\Model;

class CompanyProfile extends Model
{
    protected $fillable = [
        'user_id',
        'member_id',
        'company_name',
        'nib',
        'pic_name',
        'pic_position',
        'pic_phone',
        'akta_pendirian',
        'company_address',
        'company_phone',
        'company_email',
        'company_website',
        'company_description',
        'company_logo',
        'industry_type',
        'company_size',
        'company_verification_status',
        'verified_by',
        'verified_at',
        'free_job_post_used',
        'free_job_post_expires_at',
        'purchased_score_details',
        'em_subscription_id',
    ];

    protected $casts = [
        'free_job_post_used'      => 'boolean',
        'purchased_score_details' => 'array',
        'verified_at'             => 'datetime',
        'free_job_post_expires_at'=> 'datetime',
    ];

    // -------------------------------------------------------
    // 採番（Section 28・32 準拠）
    // 新規作成時に member_id が空であれば自動で HRI-MEM-C-YYYY-NNNNNN を発行
    // -------------------------------------------------------
    protected static function boot(): void
    {
        parent::boot();

        static::created(function (CompanyProfile $profile) {
            if (empty($profile->member_id)) {
                $number = app(NumberingService::class)->issueMemberNoForCompany();
                $profile->updateQuietly(['member_id' => $number]);
            }
        });
    }

    // -------------------------------------------------------
    // リレーション
    // -------------------------------------------------------
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function emSubscription()
    {
        return $this->belongsTo(EmSubscription::class, 'em_subscription_id');
    }

    // -------------------------------------------------------
    // ヘルパー
    // -------------------------------------------------------

    /** 会員番号が発行済みか */
    public function hasMemberNo(): bool
    {
        return !empty($this->member_id);
    }

    /** 企業審査が承認済みか */
    public function isVerified(): bool
    {
        return $this->company_verification_status === 'approved';
    }

    /** 求人無料枠が使用可能か */
    public function canUseFreejobPost(): bool
    {
        if ($this->free_job_post_used) {
            return false;
        }
        if ($this->free_job_post_expires_at && $this->free_job_post_expires_at->isPast()) {
            return false;
        }
        return true;
    }
}