<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /** @var list<string> */
    private const SUPPORTED = ['en', 'ar', 'tr', 'es', 'fr', 'de', 'ur'];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolveLocale($request);

        if (in_array($locale, self::SUPPORTED, true)) {
            App::setLocale($locale);

            Session::put('direction', in_array($locale, ['ar', 'ur'], true) ? 'rtl' : 'ltr');
        }

        return $next($request);
    }

    private function resolveLocale(Request $request): string
    {
        // Explicit request signals win (templates preview, Nuxt i18n, etc.).
        foreach ([
            $request->query('locale'),
            $request->query('language'),
            $request->header('X-Locale'),
        ] as $candidate) {
            $normalized = $this->normalize($candidate);
            if ($normalized !== null) {
                return $normalized;
            }
        }

        $header = $request->header('Accept-Language');
        if (is_string($header) && $header !== '') {
            $normalized = $this->normalize(trim(explode(',', $header)[0]));
            if ($normalized !== null) {
                return $normalized;
            }
        }

        $sessionLocale = Session::get('locale');
        if (is_string($sessionLocale)) {
            $normalized = $this->normalize($sessionLocale);
            if ($normalized !== null) {
                return $normalized;
            }
        }

        $default = config('app.locale', 'en');

        return is_string($default) ? $default : 'en';
    }

    private function normalize(mixed $value): ?string
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        $candidate = strtolower(substr(str_replace('_', '-', trim($value)), 0, 2));

        return in_array($candidate, self::SUPPORTED, true) ? $candidate : null;
    }
}
