<?php

namespace App\Models\Recruitment;

use Illuminate\Database\Eloquent\Model;

class JobListing extends Model
{
    protected $table = 'job_lists';

    protected $fillable = [
        'position',
        'description',
        'qualifications',
        'type',
        'department',
        'arrangement',
        'location',
        'expiration_date',
        'status',
    ];
}
