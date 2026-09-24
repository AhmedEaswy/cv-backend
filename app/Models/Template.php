<?php

namespace App\Models;

use App\Models\Concerns\ResolvesTemplatePreview;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Template extends Model
{
    use HasFactory, ResolvesTemplatePreview, SoftDeletes;

    protected $fillable = [
        'name',
        'preview',
        'preview_ar',
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
        'preview_ar_url',
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

    public function profiles(): HasMany
    {
        return $this->hasMany(Profile::class);
    }
}
