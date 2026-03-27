<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certification extends Model
{
    protected $table = 'certifications';

    protected $fillable = [
        'user_id',
        'certification_request_id',
        'certificate_name',
        'issuing_organization',
        'issue_date',
        'expiration_date',
        'certificate_file',
        'certificate_score',
        'certificate_notes',
        'certificate_attachment',
    ];
}