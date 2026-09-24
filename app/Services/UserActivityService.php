<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Carbon;

class UserActivityService
{
    /**
     * Record last activity and accumulate platforms for a logged-in user.
     * Throttles last_used_at writes unless a new platform is discovered.
     */
    public function touch(User $user, ?string $appPlatform): void
    {
        $platform = $this->normalizePlatform($appPlatform);
        $platforms = array_values(array_unique(array_filter(
            is_array($user->used_platforms) ? $user->used_platforms : []
        )));

        $discoveredNewPlatform = false;
        if ($platform !== null && ! in_array($platform, $platforms, true)) {
            $platforms[] = $platform;
            $discoveredNewPlatform = true;
        }

        $usesBoth = $this->detectUsesBoth($platforms);
        $shouldRefreshLastUsed = $discoveredNewPlatform
            || $user->last_used_at === null
            || $user->last_used_at->lt(now()->subMinutes(5));

        if (! $shouldRefreshLastUsed && ! $discoveredNewPlatform && (bool) $user->uses_both_platforms === $usesBoth) {
            return;
        }

        $updates = [];

        if ($shouldRefreshLastUsed) {
            $updates['last_used_at'] = Carbon::now();
            if ($platform !== null) {
                $updates['last_app_platform'] = $platform;
            }
        }

        if ($discoveredNewPlatform || (bool) $user->uses_both_platforms !== $usesBoth) {
            $updates['used_platforms'] = $platforms;
            $updates['uses_both_platforms'] = $usesBoth;
        }

        if ($updates === []) {
            return;
        }

        // Query builder avoids firing model events on high-frequency touches.
        User::query()->whereKey($user->id)->update($updates);

        $user->fill($updates);
    }

    /**
     * @param  list<string>  $platforms
     */
    public function detectUsesBoth(array $platforms): bool
    {
        $hasWeb = in_array('web', $platforms, true);
        $hasMobile = in_array('ios', $platforms, true) || in_array('android', $platforms, true);

        return $hasWeb && $hasMobile;
    }

    public function normalizePlatform(?string $platform): ?string
    {
        if ($platform === null || $platform === '') {
            return null;
        }

        $platform = strtolower(trim($platform));

        return match ($platform) {
            'ios', 'iphone', 'ipad' => 'ios',
            'android' => 'android',
            'web', 'browser' => 'web',
            default => null, // ignore api/unknown for "both" detection
        };
    }
}
