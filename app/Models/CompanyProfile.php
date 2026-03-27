<?php
namespace App\Models;
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
    ];

    protected $casts = [
        'free_job_post_used'       => 'boolean',
        'purchased_score_details'  => 'array', // ★ 追加（JSON自動変換）
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}