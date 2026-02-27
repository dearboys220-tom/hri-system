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
    ];

    protected $casts = [
        'birth_date' => 'date',
        'certification_date' => 'date',
        'certification_expiry_date' => 'date',
        'free_certification_expires_at' => 'date',
        'free_certification_used' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
