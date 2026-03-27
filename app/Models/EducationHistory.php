<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EducationHistory extends Model
{
    protected $table = 'education_history';

    protected $fillable = [
        'user_id',
        'certification_request_id',
        'education_level',
        'school_name',
        'school_location',
        'degree_name',
        'degree',
        'status',
        'ipk_gpa',
        'graduation_status',
        'ijazah_transcript',
        'enrollment_date',
        'graduation_date',
        'academic_achievements',
        'education_file',
    ];
}