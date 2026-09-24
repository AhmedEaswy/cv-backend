<?php

namespace App\Models;

use App\Models\Concerns\ResolvesTemplatePreview;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PublicProfileTemplate extends Model
{
    use HasFactory, ResolvesTemplatePreview, SoftDeletes;

    protected $fillable = [
        'name',
        'preview',
        'preview_ar',
        'description',
        'is_active',
        'is_default',
    ];

    protected $appends = [
        'preview_url',
        'preview_ar_url',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    public function publicProfiles(): HasMany
    {
        return $this->hasMany(PublicProfile::class);
    }
}
