<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CoverLetter extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'anonymous_user_id',
        'client_ref',
        'cover_letter_template_id',
        'name',
        'language',
        'is_public',
        'sections_order',
        'info',
        'experiences',
        'ip_address',
        'country',
        'device',
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
            'sections_order' => 'array',
            'info' => 'array',
            'experiences' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function anonymousUser(): BelongsTo
    {
        return $this->belongsTo(AnonymousUser::class);
    }

    /**
     * Other cover letters from the same anonymous install.
     */
    public function siblingCoverLetters(): HasMany
    {
        return $this->hasMany(self::class, 'anonymous_user_id', 'anonymous_user_id')
            ->where('cover_letters.id', '!=', $this->getKey() ?? 0);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(CoverLetterTemplate::class, 'cover_letter_template_id');
    }
}
