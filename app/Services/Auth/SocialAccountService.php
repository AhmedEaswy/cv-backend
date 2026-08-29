<?php

namespace App\Services\Auth;

use App\Enums\UserType;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUserContract;

/**
 * Handles linking a verified Google account to a local user.
 *
 * Behavior:
 *  - If a SocialAccount row already exists for (provider, providerId),
 *    return its user.
 *  - Else if a local user matches the email, link the social account
 *    to that user and return them.
 *  - Else create a brand new user (random password) and link the
 *    social account.
 *
 * The user is always returned, the caller is responsible for
 * authentication (issuing a Sanctum token or starting a session).
 */
class SocialAccountService
{
    public function findOrCreateUser(string $provider, SocialiteUserContract $socialUser): User
    {
        $providerId = (string) $socialUser->getId();
        $email = (string) $socialUser->getEmail();

        $existingLink = SocialAccount::where('provider_name', $provider)
            ->where('provider_id', $providerId)
            ->first();

        if ($existingLink) {
            $this->refreshToken($existingLink, $socialUser);

            return $existingLink->user;
        }

        $user = $email !== '' ? User::where('email', $email)->first() : null;

        if (! $user) {
            $name = trim((string) ($socialUser->getName() ?? $email)) ?: 'User';
            $parts = preg_split('/\s+/u', $name, 2) ?: [$name];

            $user = User::create([
                'name' => $name,
                'first_name' => $parts[0] ?? $name,
                'last_name' => $parts[1] ?? '',
                'email' => $email !== '' ? $email : $this->placeholderEmail($provider, $providerId),
                'password' => Hash::make(Str::random(48)),
                'type' => UserType::USER->value,
                'active' => true,
            ]);
        }

        SocialAccount::create([
            'user_id' => $user->id,
            'provider_name' => $provider,
            'provider_id' => $providerId,
            'provider_token' => $socialUser->token,
            'provider_refresh_token' => $socialUser->refreshToken,
        ]);

        if (! $user->hasVerifiedEmail() && $email !== '') {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        return $user;
    }

    private function refreshToken(SocialAccount $account, SocialiteUserContract $socialUser): void
    {
        $account->forceFill([
            'provider_token' => $socialUser->token,
            'provider_refresh_token' => $socialUser->refreshToken,
        ])->save();
    }

    private function placeholderEmail(string $provider, string $providerId): string
    {
        return $provider.'+'.$providerId.'@users.local';
    }
}
