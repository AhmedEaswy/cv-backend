<?php

namespace App\Services;

use Illuminate\Http\Request;

class TrackingService
{
    public function capture(Request $request): array
    {
        $ip = $request->ip();
        $country = $this->resolveCountry($ip);

        return [
            'ip_address' => $ip,
            'country' => $country,
            'device' => $this->resolveDevice($request->header('User-Agent')),
        ];
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

    private function resolveDevice(?string $userAgent): ?string
    {
        if (! $userAgent || $userAgent === 'Symfony') {
            return null;
        }

        $os = 'Unknown OS';
        $browser = 'Unknown Browser';

        if (str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad')) {
            $os = 'iOS';
        } elseif (str_contains($userAgent, 'Android')) {
            $os = 'Android';
        } elseif (str_contains($userAgent, 'Windows')) {
            $os = 'Windows';
        } elseif (str_contains($userAgent, 'Mac')) {
            $os = 'macOS';
        } elseif (str_contains($userAgent, 'Linux')) {
            $os = 'Linux';
        }

        if (str_contains($userAgent, 'Chrome') && ! str_contains($userAgent, 'Edg')) {
            $browser = 'Chrome';
        } elseif (str_contains($userAgent, 'Firefox')) {
            $browser = 'Firefox';
        } elseif (str_contains($userAgent, 'Safari') && ! str_contains($userAgent, 'Chrome')) {
            $browser = 'Safari';
        } elseif (str_contains($userAgent, 'Edg')) {
            $browser = 'Edge';
        } elseif (str_contains($userAgent, 'MSIE') || str_contains($userAgent, 'Trident')) {
            $browser = 'Internet Explorer';
        }

        $isMobile = str_contains($userAgent, 'Mobile') || str_contains($userAgent, 'Android');
        $deviceType = $isMobile ? 'Mobile' : 'Desktop';

        return "{$deviceType} / {$os} / {$browser}";
    }
}
