<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certification extends Model
{
    protected $table = 'certifications';
    protected $fillable = [
        'user_id',
        'certification_request_id',
        'name',
        'organization',
        'issued_date',
        'valid_until',
        'certificate_file',
        'certificate_score',
        'notes',
    ];
}