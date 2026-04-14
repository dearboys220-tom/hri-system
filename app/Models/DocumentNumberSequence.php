<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentNumberSequence extends Model
{
    protected $fillable = [
        'number_type',
        'period_key',
        'last_sequence',
        'dept_code',
    ];

    protected $casts = [
        'last_sequence' => 'integer',
    ];
}