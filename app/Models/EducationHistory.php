<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EducationHistory extends Model
{
    protected $table = 'education_history';
    protected $fillable = [
        'user_id',
        'certification_request_id',
        'level',
        'school',
        'school_location',
        'degree',
        'major',
        'status',
        'gpa',
        'graduation_status',
        'ijazah_transcript',
        'enrollment_date',
        'graduation_date',
        'achievements',
        'education_file',
    ];
}
