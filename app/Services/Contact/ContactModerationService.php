<?php

namespace App\Services\Contact;

use App\Enums\ContactMessageModerationStatus;

class ContactModerationService
{
    public function initialStatus(string $messageBody, int $publicProfileId, string $senderEmail): ContactMessageModerationStatus
    {
        if ($this->countLinks($messageBody) >= (int) config('contact.moderation.max_links_before_review', 3)) {
            return ContactMessageModerationStatus::PendingReview;
        }

        if ($this->senderIsFloodingProfile($senderEmail, $publicProfileId)) {
            return ContactMessageModerationStatus::PendingReview;
        }

        return ContactMessageModerationStatus::Approved;
    }

    /**
     * Holds review when the same sender floods a single profile (not cross-visitor campaign traffic).
     */
    public function senderIsFloodingProfile(string $email, int $publicProfileId): bool
    {
        $normalizedEmail = strtolower(trim($email));
        if ($normalizedEmail === '') {
            return false;
        }

        $windowMinutes = (int) config('contact.moderation.profile_burst_window_minutes', 15);
        $threshold = (int) config('contact.moderation.profile_burst_threshold', 5);
        $since = now()->subMinutes($windowMinutes);

        $count = \App\Models\ContactMessage::query()
            ->where('public_profile_id', $publicProfileId)
            ->whereRaw('LOWER(email) = ?', [$normalizedEmail])
            ->where('created_at', '>=', $since)
            ->count();

        return $count >= max(1, $threshold - 1);
    }

    private function countLinks(string $body): int
    {
        preg_match_all('/https?:\/\/|www\./i', $body, $matches);

        return count($matches[0] ?? []);
    }
}
