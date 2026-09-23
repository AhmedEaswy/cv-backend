<?php

namespace App\Support;

class SocialProvider
{
    public const GOOGLE = 'google';

    public const LINKEDIN = 'linkedin';

    public const APPLE = 'apple';

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [self::GOOGLE, self::LINKEDIN, self::APPLE];
    }

    public static function isSupported(string $provider): bool
    {
        return in_array($provider, self::all(), true);
    }

    /**
     * Socialite driver name. LinkedIn Sign In uses OpenID Connect.
     */
    public static function socialiteDriver(string $provider): string
    {
        return $provider === self::LINKEDIN ? 'linkedin-openid' : $provider;
    }
}
