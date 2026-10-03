<?php

namespace App\Services\Contact;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TurnstileVerifier
{
    public function isRequired(): bool
    {
        return (bool) config('contact.turnstile.enabled')
            && is_string(config('contact.turnstile.secret_key'))
            && config('contact.turnstile.secret_key') !== '';
    }

    public function siteKey(): ?string
    {
        $key = config('contact.turnstile.site_key');

        return is_string($key) && $key !== '' ? $key : null;
    }

    public function verify(?string $token, ?string $remoteIp): bool
    {
        if (! $this->isRequired()) {
            return true;
        }

        if ($token === null || trim($token) === '') {
            return false;
        }

        $secret = config('contact.turnstile.secret_key');
        if (! is_string($secret) || $secret === '') {
            return false;
        }

        try {
            $response = Http::asForm()
                ->timeout(8)
                ->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                    'secret' => $secret,
                    'response' => $token,
                    'remoteip' => $remoteIp,
                ]);

            if (! $response->successful()) {
                return false;
            }

            return (bool) $response->json('success');
        } catch (\Throwable $e) {
            Log::warning('Turnstile verification failed', ['error' => $e->getMessage()]);

            return false;
        }
    }
}
