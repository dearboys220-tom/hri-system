<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyProfile extends Model
{
    protected $fillable = [
        'user_id',
        'company_name',
        'company_logo',
        'nib',
        'pic_name',
        'pic_position',
        'pic_phone',
        'company_email',
        'company_phone',
        'company_address',
        'company_website',
        'company_description',
        'industry_type',
        'company_size',
        'company_verification_status',
        'verified_at',
        'verified_by',
        'verification_notes',
        'free_job_post_used',
        'free_job_post_expires_at',
        'purchased_score_details',
        'akta_pendirian',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'free_job_post_used' => 'boolean',
        'free_job_post_expires_at' => 'datetime',
        'purchased_score_details' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
    public function isVerified()
    {
        return $this->company_verification_status === 'verified';
    }

    public function canPostFreeJob()
    {
        return !$this->free_job_post_used
            && $this->created_at->addDays(30)->isFuture();
    }
}
