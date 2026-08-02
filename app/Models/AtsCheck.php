<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AtsCheck extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'profile_id',
        'source',
        'score',
        'grade',
        'language',
        'has_job_description',
        'keyword_coverage',
        'categories',
        'checks',
        'keywords',
        'job_description',
        'candidate_name',
        'candidate_email',
        'pdf_original_name',
        'ip_address',
        'country',
        'device',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'has_job_description' => 'boolean',
            'categories' => 'array',
            'checks' => 'array',
            'keywords' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }
}
