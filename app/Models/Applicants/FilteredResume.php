<?php

namespace App\Models\Applicants;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FilteredResume extends Model
{
    protected $fillable = [
        'application_id',
        'skills',
        'experience',
        'education',
        'rating_score',
        'qualification_status',
    ];

    // Important: Cast JSON columns to arrays automatically
    protected $casts = [
        'skills' => 'array',
        'experience' => 'array',
        'education' => 'array',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
