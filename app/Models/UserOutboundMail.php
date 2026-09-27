<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class UserOutboundMail extends Model
{
    protected $table = 'user_outbound_mail';

    protected $fillable = [
        'user_id',
        'domain',
        'from_email',
        'from_name',
        'dns_verification_token',
        'dns_verified_at',
        'smtp_host',
        'smtp_port',
        'smtp_encryption',
        'smtp_username',
        'smtp_password',
        'smtp_verified_at',
        'is_active',
    ];

    protected $hidden = [
        'smtp_password',
        'dns_verification_token',
    ];

    protected function casts(): array
    {
        return [
            'dns_verified_at' => 'datetime',
            'smtp_verified_at' => 'datetime',
            'is_active' => 'boolean',
            'smtp_port' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getSmtpPasswordAttribute(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Throwable) {
            return null;
        }
    }

    public function setSmtpPasswordAttribute(?string $value): void
    {
        if ($value === null || $value === '') {
            $this->attributes['smtp_password'] = null;

            return;
        }

        $this->attributes['smtp_password'] = Crypt::encryptString($value);
    }

    public function isReadyForSending(): bool
    {
        return $this->is_active
            && $this->dns_verified_at !== null
            && $this->smtp_verified_at !== null
            && filled($this->from_email)
            && filled($this->smtp_host)
            && filled($this->smtp_username)
            && filled($this->smtp_password);
    }
}
