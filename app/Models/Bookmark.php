<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bookmark extends Model
{
    protected $fillable = ['user_id', 'job_post_id'];

    public function jobPost()
    {
        return $this->belongsTo(JobPost::class, 'job_post_id');
    }
}