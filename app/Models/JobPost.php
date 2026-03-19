<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobPost extends Model
{
    protected $fillable = [
        'company_id',
        'title',
        'workplace_photo',
        'category',
        'employment_type',
        'education_requirement',
        'experience_level',
        'working_days',
        'working_hours',
        'language_requirements',
        'gender',
        'age_min',
        'age_max',
        'marital_status',
        'salary_min',
        'salary_max',
        'location',
        'job_description',
        'required_skills',
        'preferred_skills',
        'application_deadline',
        'start_date',
        'special_requirements',
        'status',
        'is_free_post',
        'views',
        'application_count',
    ];

    protected $casts = [
        'application_deadline'  => 'date',
        'start_date'            => 'date',
        'is_free_post'          => 'boolean',
        'salary_min'            => 'integer',
        'salary_max'            => 'integer',
        'working_days'          => 'array',
        'language_requirements' => 'array',
    ];

    public function company()
    {
        return $this->belongsTo(User::class, 'company_id');
    }

    public function applications()
    {
        return $this->hasMany(JobApplication::class, 'job_post_id');
    }
}