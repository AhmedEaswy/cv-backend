<?php

namespace App\Services\Contact;

use App\Models\ContactMessage;
use App\Models\ContactSpamBlocklist;
use Illuminate\Support\Facades\DB;

class ContactSpamService
{
    public function isBlocked(?string $email, ?string $ip): bool
    {
        $normalizedEmail = $this->normalizeEmail($email);

        if ($normalizedEmail === null && ($ip === null || $ip === '')) {
            return false;
        }

        return ContactSpamBlocklist::query()
            ->where(function ($q) use ($normalizedEmail, $ip) {
                if ($normalizedEmail !== null) {
                    $q->orWhere('email', $normalizedEmail);
                }
                if ($ip !== null && $ip !== '') {
                    $q->orWhere('ip_address', $ip);
                }
            })
            ->exists();
    }

    public function block(
        ?string $email,
        ?string $ip,
        string $reason,
        ?int $messageId = null,
        ?int $userId = null,
    ): void {
        $normalizedEmail = $this->normalizeEmail($email);
        $ip = ($ip !== null && $ip !== '') ? $ip : null;

        if ($normalizedEmail === null && $ip === null) {
            return;
        }

        // Ensure both email and IP are covered (may create/update separate rows).
        if ($normalizedEmail !== null) {
            $this->upsertIdentifier('email', $normalizedEmail, $ip, $reason, $messageId, $userId);
        }

        if ($ip !== null) {
            $this->upsertIdentifier('ip_address', $ip, $normalizedEmail, $reason, $messageId, $userId);
        }
    }

    public function recordHoneypot(?string $email, ?string $ip, ?int $messageId = null): void
    {
        $this->block($email, $ip, 'honeypot', $messageId);
    }

    public function maybeAutoBlockCrossProfile(?string $email, ?string $ip): void
    {
        $normalizedEmail = $this->normalizeEmail($email);
        $since = now()->subHour();

        $query = ContactMessage::query()
            ->where('created_at', '>=', $since)
            ->where(function ($q) use ($normalizedEmail, $ip) {
                if ($normalizedEmail !== null) {
                    $q->orWhereRaw('LOWER(email) = ?', [$normalizedEmail]);
                }
                if ($ip !== null && $ip !== '') {
                    $q->orWhere('ip_address', $ip);
                }
            });

        $distinctProfiles = (int) $query
            ->clone()
            ->select(DB::raw('count(distinct public_profile_id) as aggregate'))
            ->value('aggregate');

        if ($distinctProfiles >= 3) {
            $this->block($email, $ip, 'cross_profile');
        }
    }

    private function upsertIdentifier(
        string $field,
        string $value,
        ?string $companion,
        string $reason,
        ?int $messageId,
        ?int $userId,
    ): void {
        $existing = ContactSpamBlocklist::query()->where($field, $value)->first();

        if ($existing) {
            $updates = [];
            if ($field === 'email' && $companion && ! $existing->ip_address) {
                $updates['ip_address'] = $companion;
            }
            if ($field === 'ip_address' && $companion && ! $existing->email) {
                $updates['email'] = $companion;
            }
            if ($updates !== []) {
                $existing->forceFill($updates)->save();
            }

            return;
        }

        ContactSpamBlocklist::create([
            'email' => $field === 'email' ? $value : $companion,
            'ip_address' => $field === 'ip_address' ? $value : $companion,
            'reason' => $reason,
            'source_message_id' => $messageId,
            'reported_by_user_id' => $userId,
        ]);
    }

    private function normalizeEmail(?string $email): ?string
    {
        if ($email === null || trim($email) === '') {
            return null;
        }

        return strtolower(trim($email));
    }
}
