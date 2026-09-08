<?php

namespace App\Services\Portal;

use App\Models\AtsCheck;
use App\Models\ContactMessage;
use App\Models\CoverLetter;
use App\Models\Profile;
use App\Models\PublicProfile;
use App\Models\User;

class PortalStatsService
{
    /**
     * Aggregate counts and last-activity data used by the dashboard / sidebar.
     *
     * @return array<string, mixed>
     */
    public function forUser(User $user): array
    {
        $cvsCount = Profile::where('user_id', $user->id)->count();
        $coverLettersCount = CoverLetter::where('user_id', $user->id)->count();
        $publicProfile = PublicProfile::where('user_id', $user->id)->first();

        $unreadMessages = $publicProfile
            ? ContactMessage::where('public_profile_id', $publicProfile->id)
                ->whereNull('read_at')
                ->count()
            : 0;

        $topAtsScore = AtsCheck::where('user_id', $user->id)->max('score');

        $latestCv = Profile::where('user_id', $user->id)
            ->latest('updated_at')
            ->first();

        $latestCoverLetter = CoverLetter::where('user_id', $user->id)
            ->latest('updated_at')
            ->first();

        return [
            'cvs_count' => $cvsCount,
            'cover_letters_count' => $coverLettersCount,
            'top_ats_score' => $topAtsScore !== null ? (int) $topAtsScore : null,
            'views_count' => (int) ($publicProfile?->views_count ?? 0),
            'unread_messages' => $unreadMessages,
            'has_public_profile' => $publicProfile !== null,
            'public_profile_is_published' => $publicProfile?->is_public ?? false,
            'public_profile_slug' => $publicProfile?->slug,
            'public_profile_url' => $publicProfile?->public_url,
            'latest_cv' => $latestCv ? [
                'id' => $latestCv->id,
                'name' => $latestCv->name,
                'updated_at' => $latestCv->updated_at,
            ] : null,
            'latest_cover_letter' => $latestCoverLetter ? [
                'id' => $latestCoverLetter->id,
                'name' => $latestCoverLetter->name,
                'updated_at' => $latestCoverLetter->updated_at,
            ] : null,
        ];
    }
}
