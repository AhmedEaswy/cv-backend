<?php

namespace App\Services\Contact;

use App\Enums\ContactMessageModerationStatus;
use App\Models\ContactMessage;

class ContactModerationService
{
    public function initialStatus(string $messageBody, int $publicProfileId): ContactMessageModerationStatus
    {
        if ($this->countLinks($messageBody) >= (int) config('contact.moderation.max_links_before_review', 3)) {
            return ContactMessageModerationStatus::PendingReview;
        }

        if ($this->profileIsInBurst($publicProfileId)) {
            return ContactMessageModerationStatus::PendingReview;
        }

        return ContactMessageModerationStatus::Approved;
    }

    public function profileIsInBurst(int $publicProfileId): bool
    {
        $windowMinutes = (int) config('contact.moderation.profile_burst_window_minutes', 15);
        $threshold = (int) config('contact.moderation.profile_burst_threshold', 5);
        $since = now()->subMinutes($windowMinutes);

        $count = ContactMessage::query()
            ->where('public_profile_id', $publicProfileId)
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
