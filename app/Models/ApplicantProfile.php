<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicantProfile extends Model
{
    protected $fillable = [
        'user_id',
        'member_id',
        'nik',
        'ktp_card',
        'ktp_address',
        'full_name',
        'gender',
        'birth_date',
        'nationality',
        'marital_status',
        'phone_number',
        'whatsapp_number',
        'current_address',
        'profile_photo',
        'self_pr',
        'certification_status',
        'certification_date',
        'certification_expiry_date',
        'hri_score',
        'free_certification_used',
        'free_certification_expires_at',
        // v2.5追加
        'data_retention_expires_at',
    ];

    protected $casts = [
        'birth_date'                    => 'date',
        'certification_date'            => 'date',
        'certification_expiry_date'     => 'date',
        'free_certification_expires_at' => 'datetime',
        'free_certification_used'       => 'boolean',
        // v2.5追加
        'data_retention_expires_at'     => 'date',
    ];

    // -------------------------------------------------------
    // リレーション
    // -------------------------------------------------------
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function certificationRequests()
    {
        return $this->hasMany(CertificationRequest::class, 'user_id', 'user_id');
    }

    // -------------------------------------------------------
    // ヘルパー
    // -------------------------------------------------------

    /** データ保存期限が到来しているか（PDP法対応） */
    public function isRetentionExpired(): bool
    {
        return $this->data_retention_expires_at
            && $this->data_retention_expires_at->isPast();
    }
}
