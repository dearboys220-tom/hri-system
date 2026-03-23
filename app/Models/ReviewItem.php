<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewItem extends Model
{
    protected $fillable = [
        'certification_request_id',
        'category',
        'item_name',
        'max_deduction',
        'actual_deduction',
        'weight',
        'notes',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function certificationRequest()
    {
        return $this->belongsTo(CertificationRequest::class);
    }
}