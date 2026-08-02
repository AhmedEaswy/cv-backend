<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class PublicProfile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'public_profile_template_id',
        'slug',
        'is_public',
        'language',
        'headline',
        'about',
        'info',
        'social_links',
        'experiences',
        'educations',
        'projects',
        'skills',
        'languages',
        'services',
        'testimonials',
        'certifications',
        'achievements',
        'availability',
        'cta',
        'sections_order',
        'seo',
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
            'info' => 'array',
            'social_links' => 'array',
            'experiences' => 'array',
            'educations' => 'array',
            'projects' => 'array',
            'skills' => 'array',
            'languages' => 'array',
            'services' => 'array',
            'testimonials' => 'array',
            'certifications' => 'array',
            'achievements' => 'array',
            'availability' => 'array',
            'cta' => 'array',
            'sections_order' => 'array',
            'seo' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (PublicProfile $profile) {
            if (blank($profile->slug)) {
                $profile->slug = static::generateUniqueSlug($profile);
            } else {
                $profile->slug = Str::slug($profile->slug);
            }
        });
    }

    public static function generateUniqueSlug(PublicProfile $profile): string
    {
        $info = $profile->info ?? [];
        $base = trim(($info['firstName'] ?? '').' '.($info['lastName'] ?? ''));

        if ($base === '') {
            $base = 'profile-'.($profile->user_id ?? Str::random(6));
        }

        $slug = Str::slug($base) ?: 'profile';
        $original = $slug;
        $counter = 1;

        while (
            static::withTrashed()
                ->where('slug', $slug)
                ->when($profile->exists, fn ($q) => $q->where('id', '!=', $profile->id))
                ->exists()
        ) {
            $slug = $original.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(PublicProfileTemplate::class, 'public_profile_template_id');
    }

    public function getPublicUrlAttribute(): string
    {
        return url('/u/'.$this->slug);
    }
}
