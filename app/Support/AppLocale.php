<?php

namespace App\Support;

final class AppLocale
{
    /** @var list<string> */
    public const SUPPORTED = ['en', 'ar', 'tr', 'es', 'fr', 'de', 'ur'];

    public static function isSupported(string $locale): bool
    {
        return in_array($locale, self::SUPPORTED, true);
    }

    public static function fallback(): string
    {
        $default = config('app.locale', 'en');

        return is_string($default) && self::isSupported($default) ? $default : 'en';
    }
}
