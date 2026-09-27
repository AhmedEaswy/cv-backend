<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactSpamBlocklist extends Model
{
    protected $table = 'contact_spam_blocklist';

    protected $fillable = [
        'email',
        'ip_address',
        'reason',
        'source_message_id',
        'reported_by_user_id',
    ];

    public function sourceMessage(): BelongsTo
    {
        return $this->belongsTo(ContactMessage::class, 'source_message_id');
    }

    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by_user_id');
    }
}
