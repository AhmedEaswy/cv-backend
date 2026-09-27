<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Services\Profile\ProfileDomain;
use Illuminate\Support\Str;

class PublicProfile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'public_profile_template_id',
        'slug',
        'is_public',
        'views_count',
        'enable_contact_form',
        'enable_inbox',
        'enable_subdomain',
        'profile_url_mode',
        'custom_domain',
        'custom_domain_dns_token',
        'custom_domain_verified_at',
        'contact_form_recipient',
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
            'views_count' => 'integer',
            'enable_contact_form' => 'boolean',
            'enable_inbox' => 'boolean',
            'enable_subdomain' => 'boolean',
            'custom_domain_verified_at' => 'datetime',
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

            $profile->syncProfileUrlModeFields();

            if ($profile->isDirty('custom_domain') && filled($profile->custom_domain)) {
                $profile->custom_domain = strtolower(trim((string) $profile->custom_domain));
                $profile->custom_domain_verified_at = null;
                if (blank($profile->custom_domain_dns_token)) {
                    $profile->custom_domain_dns_token = Str::random(32);
                }
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
        return $this->preferredPublicUrl();
    }

    public function pathUrl(): string
    {
        return app(ProfileDomain::class)->pathUrl((string) $this->slug);
    }

    public function subdomainUrl(): ?string
    {
        if ($this->profileUrlMode() !== 'subdomain') {
            return null;
        }

        return app(ProfileDomain::class)->subdomainUrl((string) $this->slug);
    }

    public function customDomainUrl(): ?string
    {
        if ($this->profileUrlMode() !== 'custom_domain') {
            return null;
        }

        if (! filled($this->custom_domain) || ! $this->custom_domain_verified_at) {
            return null;
        }

        $scheme = parse_url((string) config('app.url'), PHP_URL_SCHEME) ?: 'https';

        return $scheme.'://'.strtolower((string) $this->custom_domain);
    }

    public function profileUrlMode(): string
    {
        $mode = $this->profile_url_mode ?: 'slug';

        if (! in_array($mode, ['slug', 'subdomain', 'custom_domain'], true)) {
            return 'slug';
        }

        return $mode;
    }

    public function syncProfileUrlModeFields(): void
    {
        if (($this->profile_url_mode === null || $this->profile_url_mode === 'slug')
            && $this->enable_subdomain) {
            $this->profile_url_mode = 'subdomain';
        }

        if ($this->isDirty('enable_subdomain') && ! $this->isDirty('profile_url_mode')) {
            $this->profile_url_mode = $this->enable_subdomain ? 'subdomain' : 'slug';
        }

        $mode = $this->profileUrlMode();
        $this->enable_subdomain = $mode === 'subdomain';

        if ($mode !== 'custom_domain') {
            return;
        }

        if (blank($this->custom_domain_dns_token) && filled($this->custom_domain)) {
            $this->custom_domain_dns_token = Str::random(32);
        }
    }

    public function preferredPublicUrl(): string
    {
        $custom = $this->customDomainUrl();
        if ($custom !== null) {
            return $custom;
        }

        if ($this->profileUrlMode() === 'subdomain') {
            return $this->subdomainUrl() ?? $this->pathUrl();
        }

        return $this->pathUrl();
    }

    public function inboxIsEnabled(): bool
    {
        return (bool) ($this->enable_inbox ?? true);
    }

    /**
     * Whether the contact form is enabled and ready to render.
     */
    public function showsContactForm(): bool
    {
        return $this->is_public
            && $this->inboxIsEnabled()
            && (bool) $this->enable_contact_form;
    }

    /**
     * Inbox of contact messages addressed to the owner of this profile.
     */
    public function contactMessages(): HasMany
    {
        return $this->hasMany(ContactMessage::class)->latest();
    }

    /**
     * Recipient email for the contact form (falls back to owner's email).
     */
    public function contactRecipient(): ?string
    {
        if (! empty($this->contact_form_recipient) && filter_var($this->contact_form_recipient, FILTER_VALIDATE_EMAIL)) {
            return $this->contact_form_recipient;
        }

        return $this->user?->email;
    }
}
