<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'user_id',
        'payment_type',
        'amount',
        'is_free',
        'payment_method',
        'payment_status',
        'payment_date',
        'midtrans_order_id',
        'midtrans_transaction_id',
        'midtrans_snap_token',
        'target_member_id',
        'related_certification_id',
        'related_job_post_id',
    ];

    protected $casts = [
        'is_free' => 'boolean',
        'amount' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function certificationRequest()
    {
        return $this->belongsTo(CertificationRequest::class, 'related_certification_id');
    }
}