<?php

namespace App\Services\Profile;

class ProfileDomain
{
    public function apexDomain(): string
    {
        $configured = config('app.profile_domain');

        if (is_string($configured) && $configured !== '') {
            return strtolower($configured);
        }

        $host = parse_url((string) config('app.url'), PHP_URL_HOST);

        return is_string($host) && $host !== '' ? strtolower($host) : 'localhost';
    }

    /**
     * @return list<string>
     */
    public function reservedSubdomains(): array
    {
        return [
            'www',
            'api',
            'app',
            'admin',
            'mail',
            'ftp',
            'cdn',
            'static',
            'portal',
            'mcp',
            'auth',
            'test',
            'staging',
            'dev',
            'localhost',
        ];
    }

    public function isReserved(string $label): bool
    {
        $normalized = strtolower(trim($label));

        return in_array($normalized, $this->reservedSubdomains(), true);
    }

    public function subdomainUrl(string $slug): string
    {
        $scheme = parse_url((string) config('app.url'), PHP_URL_SCHEME) ?: 'https';

        return $scheme.'://'.strtolower($slug).'.'.$this->apexDomain();
    }

    public function pathUrl(string $slug): string
    {
        return url('/u/'.$slug);
    }

    /**
     * Extract profile slug from request host when on a vanity subdomain.
     */
    public function slugFromHost(?string $host): ?string
    {
        if ($host === null || $host === '') {
            return null;
        }

        $host = strtolower($host);
        $apex = $this->apexDomain();

        $suffix = '.'.$apex;
        if (! str_ends_with($host, $suffix)) {
            return null;
        }

        $label = substr($host, 0, -strlen($suffix));
        if ($label === '' || str_contains($label, '.')) {
            return null;
        }

        if ($this->isReserved($label)) {
            return null;
        }

        return $label;
    }
}
