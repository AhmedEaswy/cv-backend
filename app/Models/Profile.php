<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Profile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'anonymous_user_id',
        'client_ref',
        'name',
        'language',
        'is_public',
        'template_id',
        'sections_order',
        'interests',
        'languages',
        'info',
        'experiences',
        'projects',
        'educations',
        'ip_address',
        'country',
        'device',
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
            'sections_order' => 'array',
            'interests' => 'array',
            'languages' => 'array',
            'info' => 'array',
            'experiences' => 'array',
            'projects' => 'array',
            'educations' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function anonymousUser(): BelongsTo
    {
        return $this->belongsTo(AnonymousUser::class);
    }

    /**
     * Other profiles from the same anonymous install.
     */
    public function siblingProfiles(): HasMany
    {
        return $this->hasMany(self::class, 'anonymous_user_id', 'anonymous_user_id')
            ->where('profiles.id', '!=', $this->getKey() ?? 0);
    }

    /**
     * Get the template associated with the profile.
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    public function atsChecks(): HasMany
    {
        return $this->hasMany(AtsCheck::class);
    }
}
