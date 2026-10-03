<?php

namespace App\Models;

use App\Enums\ContactMessageModerationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContactMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'public_profile_id',
        'user_id',
        'name',
        'email',
        'subject',
        'message',
        'ip_address',
        'user_agent',
        'read_at',
        'hidden_at',
        'is_spam',
        'moderation_status',
        'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
            'hidden_at' => 'datetime',
            'is_spam' => 'boolean',
            'moderation_status' => ContactMessageModerationStatus::class,
            'delivered_at' => 'datetime',
        ];
    }

    public function publicProfile(): BelongsTo
    {
        return $this->belongsTo(PublicProfile::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(ContactMessageReply::class)->latest();
    }

    /**
     * @param  Builder<ContactMessage>  $query
     * @return Builder<ContactMessage>
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query
            ->whereNull('hidden_at')
            ->where('moderation_status', ContactMessageModerationStatus::Approved->value);
    }

    public function isDelivered(): bool
    {
        return $this->delivered_at !== null;
    }

    public function awaitsModeration(): bool
    {
        return $this->moderation_status === ContactMessageModerationStatus::PendingReview;
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    public function markAsRead(): void
    {
        if (! $this->isRead()) {
            $this->forceFill(['read_at' => now()])->save();
        }
    }
}
