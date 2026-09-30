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
     * Whether the provider is turned on via env (LINKEDIN_AUTH_ENABLED / APPLE_AUTH_ENABLED).
     */
    public static function isEnabled(string $provider): bool
    {
        return match ($provider) {
            self::LINKEDIN => (bool) config('services.linkedin.enabled', true),
            self::APPLE => (bool) config('services.apple.enabled', true),
            self::GOOGLE => true,
            default => false,
        };
    }

    /**
     * Enabled and has OAuth credentials, so the web redirect flow can run.
     */
    public static function isAvailable(string $provider): bool
    {
        if (! self::isSupported($provider) || ! self::isEnabled($provider)) {
            return false;
        }

        $configKey = $provider === self::LINKEDIN ? 'linkedin-openid' : $provider;

        return (bool) config("services.{$configKey}.client_id");
    }

    /**
     * Socialite driver name. LinkedIn Sign In uses OpenID Connect.
     */
    public static function socialiteDriver(string $provider): string
    {
        return $provider === self::LINKEDIN ? 'linkedin-openid' : $provider;
    }
}
