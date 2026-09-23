<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\SocialAccountService;
use App\Support\SocialProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Laravel\Socialite\Contracts\Provider as SocialiteProvider;
use Laravel\Socialite\Facades\Socialite;
use SocialiteProviders\Apple\Provider as AppleProvider;
use Throwable;

class AppleAuthController extends Controller
{
    public function __construct(private readonly SocialAccountService $socialAccounts) {}

    public function redirect(Request $request): RedirectResponse
    {
        if (! config('services.apple.client_id')) {
            return redirect()->to($this->frontendUrl('/auth/login?error=social'));
        }

        $request->session()->put(
            'url.intended',
            $this->safeReturnTo($request->query('return_to'))
        );

        return $this->driver()->redirect();
    }

    public function callback(Request $request): RedirectResponse|View
    {
        $intended = $this->safeReturnTo(
            $request->session()->pull('url.intended')
        );

        try {
            $socialUser = $this->driver()->user();
        } catch (Throwable $e) {
            Log::warning('Apple social auth failed', ['error' => $e->getMessage()]);

            return redirect()->to($this->frontendUrl('/auth/login?error=social'));
        }

        $user = $this->socialAccounts->findOrCreateUser(SocialProvider::APPLE, $socialUser);

        Auth::login($user, true);
        $request->session()->regenerate();

        if (! $user->hasVerifiedEmail()) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        $token = $user->createToken('apple_auth')->plainTextToken;

        return view('auth.google-handoff', [
            'token' => $token,
            'redirect' => $intended,
        ]);
    }

    private function driver(): SocialiteProvider|AppleProvider
    {
        /** @var AppleProvider $driver */
        $driver = Socialite::driver(SocialProvider::APPLE)
            ->stateless()
            ->redirectUrl($this->callbackUrl());

        // Apple form_post is cross-site; carry nonce in a SameSite=none cookie
        // instead of relying on the Laravel session cookie.
        if (method_exists($driver, 'cookieNonce')) {
            $driver->cookieNonce();
        }

        return $driver;
    }

    private function callbackUrl(): string
    {
        return route('auth.apple.callback');
    }

    private function frontendUrl(string $path = '/'): string
    {
        return rtrim((string) config('app.frontend_url'), '/').'/'.ltrim($path, '/');
    }

    /**
     * Only allow same-origin (frontend) absolute URLs or relative paths.
     */
    private function safeReturnTo(mixed $candidate): string
    {
        $default = $this->frontendUrl('/portal');
        $value = is_string($candidate) ? trim($candidate) : '';

        if ($value === '') {
            return $default;
        }

        if (str_starts_with($value, '/') && ! str_starts_with($value, '//')) {
            return $this->frontendUrl($value);
        }

        $frontend = rtrim((string) config('app.frontend_url'), '/');
        if (str_starts_with($value, $frontend.'/') || $value === $frontend) {
            return $value;
        }

        return $default;
    }
}
