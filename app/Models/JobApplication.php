<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    protected $fillable = [
        'job_post_id',
        'applicant_id',
        'company_id',
        'status',
        'applied_at',
        'company_notes',
        'applicant_snapshot',
        'job_deleted',
    ];

    protected $casts = [
        'applicant_snapshot' => 'array',
        'job_deleted'        => 'boolean',
    ];

    // ★ 追加：応募した求人
    public function jobPost()
    {
        return $this->belongsTo(JobPost::class, 'job_post_id');
    }

    // ★ 追加：応募者
    public function applicant()
    {
        return $this->belongsTo(User::class, 'applicant_id');
    }

    // ★ 追加：企業
    public function company()
    {
        return $this->belongsTo(User::class, 'company_id');
    }
}