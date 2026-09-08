<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Template extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'preview',
        'description',
        'is_active',
        'is_default',
        'supports_image',
    ];

    protected $attributes = [
        'supports_image' => false,
    ];

    protected $appends = [
        'preview_url',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'supports_image' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function getPreviewUrlAttribute(): ?string
    {
        if (! $this->preview) {
            return null;
        }

        if (str_starts_with($this->preview, 'http://') || str_starts_with($this->preview, 'https://')) {
            return $this->preview;
        }

        // Shipped assets in public/ (same pattern as cover-letter templates).
        if (str_starts_with($this->preview, 'images/')) {
            return asset($this->preview);
        }

        return Storage::disk('public')->url($this->preview);
    }

    public function profiles(): HasMany
    {
        return $this->hasMany(Profile::class);
    }
}
