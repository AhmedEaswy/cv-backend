<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class CoverLetterTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'preview',
        'description',
        'is_active',
        'is_default',
    ];

    protected $appends = [
        'preview_url',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_default' => 'boolean',
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

        if (str_starts_with($this->preview, 'images/')) {
            return asset($this->preview);
        }

        return Storage::disk('public')->url($this->preview);
    }
}
