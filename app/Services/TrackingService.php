<?php

namespace App\Services;

use Illuminate\Http\Request;

class TrackingService
{
    /**
     * Capture geo + structured device/app metadata from the request.
     *
     * Prefers client headers (X-App-Platform, X-App-Version, X-OS-Version,
     * X-Device-Model) and falls back to User-Agent parsing.
     *
     * @return array{
     *     ip_address: string|null,
     *     country: string|null,
     *     device: string|null,
     *     device_type: string|null,
     *     os: string|null,
     *     os_version: string|null,
     *     browser: string|null,
     *     device_model: string|null,
     *     app_platform: string|null,
     *     app_version: string|null,
     *     locale: string|null
     * }
     */
    public function capture(Request $request): array
    {
        $ip = $request->ip();
        $userAgent = $request->header('User-Agent');
        $parsed = $this->parseUserAgent($userAgent);

        $deviceType = $this->headerOrNull($request, 'X-Device-Type') ?? $parsed['device_type'];
        $os = $parsed['os'];
        $osVersion = $this->headerOrNull($request, 'X-OS-Version') ?? $parsed['os_version'];
        $browser = $parsed['browser'];
        $deviceModel = $this->headerOrNull($request, 'X-Device-Model') ?? $parsed['device_model'];
        $appPlatform = $this->normalizePlatform(
            $this->headerOrNull($request, 'X-App-Platform') ?? $this->inferPlatform($deviceType, $os, $userAgent)
        );
        $appVersion = $this->headerOrNull($request, 'X-App-Version');
        $locale = $this->resolveLocale($request);

        $device = $this->composeDeviceLabel($deviceType, $os, $browser, $deviceModel);

        return [
            'ip_address' => $ip,
            'country' => $this->resolveCountry((string) $ip),
            'device' => $device,
            'device_type' => $deviceType,
            'os' => $os,
            'os_version' => $osVersion,
            'browser' => $browser,
            'device_model' => $deviceModel,
            'app_platform' => $appPlatform,
            'app_version' => $appVersion,
            'locale' => $locale,
        ];
    }

    /**
     * Subset safe to mass-assign onto profiles / cover_letters.
     *
     * @return array{ip_address: string|null, country: string|null, device: string|null}
     */
    public function captureContent(Request $request): array
    {
        $full = $this->capture($request);

        return [
            'ip_address' => $full['ip_address'],
            'country' => $full['country'],
            'device' => $full['device'],
        ];
    }

    private function headerOrNull(Request $request, string $name): ?string
    {
        $value = $request->header($name);

        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        if ($value === '' || strlen($value) > 128) {
            return null;
        }

        return $value;
    }

    private function resolveLocale(Request $request): ?string
    {
        $header = $request->header('Accept-Language');

        if (! is_string($header) || $header === '') {
            return null;
        }

        $primary = strtolower(trim(explode(',', $header)[0] ?? ''));
        $primary = explode(';', $primary)[0] ?? '';
        $primary = trim($primary);

        return $primary !== '' && strlen($primary) <= 32 ? $primary : null;
    }

    private function normalizePlatform(?string $platform): ?string
    {
        if ($platform === null) {
            return null;
        }

        $platform = strtolower(trim($platform));

        return match ($platform) {
            'ios', 'iphone', 'ipad' => 'ios',
            'android' => 'android',
            'web', 'browser' => 'web',
            'api', 'server' => 'api',
            default => strlen($platform) <= 32 ? $platform : null,
        };
    }

    private function inferPlatform(?string $deviceType, ?string $os, ?string $userAgent): ?string
    {
        $ua = strtolower((string) $userAgent);

        if (str_contains($ua, 'okhttp') || str_contains($ua, 'dart:') || str_contains($ua, 'cfnetwork')) {
            return match ($os) {
                'iOS' => 'ios',
                'Android' => 'android',
                default => 'api',
            };
        }

        if (in_array($os, ['iOS', 'Android'], true) && $deviceType === 'Mobile') {
            // Could be mobile browser; without X-App-Platform prefer web when a browser is present.
            return null;
        }

        return 'web';
    }

    /**
     * @return array{
     *     device_type: string|null,
     *     os: string|null,
     *     os_version: string|null,
     *     browser: string|null,
     *     device_model: string|null
     * }
     */
    private function parseUserAgent(?string $userAgent): array
    {
        if (! $userAgent || $userAgent === 'Symfony') {
            return [
                'device_type' => null,
                'os' => null,
                'os_version' => null,
                'browser' => null,
                'device_model' => null,
            ];
        }

        $os = 'Unknown OS';
        $osVersion = null;
        $browser = 'Unknown Browser';
        $deviceModel = null;
        $deviceType = 'Desktop';

        if (preg_match('/iPhone/i', $userAgent)) {
            $os = 'iOS';
            $deviceType = 'Mobile';
            $deviceModel = 'iPhone';
            if (preg_match('/OS (\d+[_\.]\d+(?:[_\.]\d+)?)/', $userAgent, $m)) {
                $osVersion = str_replace('_', '.', $m[1]);
            }
        } elseif (preg_match('/iPad/i', $userAgent)) {
            $os = 'iOS';
            $deviceType = 'Tablet';
            $deviceModel = 'iPad';
            if (preg_match('/OS (\d+[_\.]\d+(?:[_\.]\d+)?)/', $userAgent, $m)) {
                $osVersion = str_replace('_', '.', $m[1]);
            }
        } elseif (preg_match('/Android/i', $userAgent)) {
            $os = 'Android';
            $deviceType = preg_match('/Mobile/i', $userAgent) ? 'Mobile' : 'Tablet';
            if (preg_match('/Android (\d+(?:\.\d+)*)/', $userAgent, $m)) {
                $osVersion = $m[1];
            }
            // "Linux; Android 14; Pixel 8 Build/..." or "...; SM-G991B ..."
            if (preg_match('/Android [^;]+; ([^;\)]+?)(?:\s+Build|\))/', $userAgent, $m)) {
                $candidate = trim($m[1]);
                if ($candidate !== '' && ! preg_match('/^(wv|U)$/i', $candidate)) {
                    $deviceModel = $candidate;
                }
            }
        } elseif (preg_match('/Windows NT (\d+\.\d+)/', $userAgent, $m)) {
            $os = 'Windows';
            $osVersion = $m[1];
        } elseif (preg_match('/Mac OS X (\d+[_\.]\d+(?:[_\.]\d+)?)/', $userAgent, $m)) {
            $os = 'macOS';
            $osVersion = str_replace('_', '.', $m[1]);
        } elseif (preg_match('/Linux/i', $userAgent)) {
            $os = 'Linux';
        }

        if (preg_match('/Edg\/(\d+)/', $userAgent, $m)) {
            $browser = 'Edge';
        } elseif (preg_match('/Chrome\/(\d+)/', $userAgent) && ! preg_match('/Edg/', $userAgent)) {
            $browser = 'Chrome';
        } elseif (preg_match('/Firefox\/(\d+)/', $userAgent)) {
            $browser = 'Firefox';
        } elseif (preg_match('/Safari\//', $userAgent) && ! preg_match('/Chrome|CriOS/', $userAgent)) {
            $browser = 'Safari';
        } elseif (preg_match('/MSIE |Trident\//', $userAgent)) {
            $browser = 'Internet Explorer';
        } elseif (preg_match('/okhttp/i', $userAgent) || preg_match('/Dart\//i', $userAgent)) {
            $browser = null;
        }

        return [
            'device_type' => $deviceType,
            'os' => $os,
            'os_version' => $osVersion,
            'browser' => $browser,
            'device_model' => $deviceModel,
        ];
    }

    private function composeDeviceLabel(
        ?string $deviceType,
        ?string $os,
        ?string $browser,
        ?string $deviceModel
    ): ?string {
        $parts = array_filter([
            $deviceType,
            $os,
            $deviceModel,
            $browser,
        ]);

        return $parts === [] ? null : implode(' / ', $parts);
    }

    private function resolveCountry(string $ip): ?string
    {
        if ($ip === '127.0.0.1' || $ip === '::1' || str_starts_with($ip, '192.168.') || str_starts_with($ip, '10.')) {
            return null;
        }

        try {
            $position = \Stevebauman\Location\Facades\Location::get($ip);

            return $position?->countryName ?? null;
        } catch (\Throwable) {
            return null;
        }
    }
}
