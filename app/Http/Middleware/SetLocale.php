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
        $header = $request->header('Accept-Language');
        if (is_string($header) && $header !== '') {
            $candidate = strtolower(substr(trim(explode(',', $header)[0]), 0, 2));
            if (in_array($candidate, self::SUPPORTED, true)) {
                return $candidate;
            }
        }

        $sessionLocale = Session::get('locale');
        if (is_string($sessionLocale) && in_array($sessionLocale, self::SUPPORTED, true)) {
            return $sessionLocale;
        }

        $default = config('app.locale', 'en');

        return is_string($default) ? $default : 'en';
    }
}
