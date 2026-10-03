<?php

namespace App\Models;

use App\Enums\FeatureRequestStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeatureRequest extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'body',
        'status',
        'is_published',
        'vote_count',
    ];

    protected function casts(): array
    {
        return [
            'status' => FeatureRequestStatus::class,
            'is_published' => 'boolean',
            'vote_count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(FeatureRequestVote::class);
    }

    /**
     * @param  Builder<FeatureRequest>  $query
     * @return Builder<FeatureRequest>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }
}
