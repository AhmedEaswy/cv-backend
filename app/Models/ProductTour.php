<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductTour extends Model
{
    protected $fillable = [
        'key',
        'is_enabled',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function steps(): HasMany
    {
        return $this->hasMany(ProductTourStep::class)->orderBy('sort_order');
    }

    public function progress(): HasMany
    {
        return $this->hasMany(UserTourProgress::class);
    }
}
