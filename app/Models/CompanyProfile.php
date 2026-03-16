<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyProfile extends Model
{
    protected $fillable = [
        'user_id',
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
        'free_job_post_used',
        'free_job_post_expires_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}