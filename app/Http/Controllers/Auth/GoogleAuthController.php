<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\SocialAccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    public function __construct(private readonly SocialAccountService $socialAccounts)
    {
    }

    public function redirect(Request $request): RedirectResponse
    {
        $request->session()->put(
            'url.intended',
            $this->safeReturnTo($request->query('return_to'))
        );

        return Socialite::driver('google')
            ->stateless()
            ->redirectUrl($this->callbackUrl())
            ->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        $intended = $this->safeReturnTo(
            $request->session()->pull('url.intended')
        );

        try {
            // redirectUrl MUST match the authorize step or Google returns
            // redirect_uri_mismatch / invalid_grant and login silently fails.
            $socialUser = Socialite::driver('google')
                ->stateless()
                ->redirectUrl($this->callbackUrl())
                ->user();
        } catch (Throwable $e) {
            Log::warning('Google social auth failed', ['error' => $e->getMessage()]);

            return redirect()->to($this->frontendUrl('/auth/login?error=social'));
        }

        $user = $this->socialAccounts->findOrCreateUser('google', $socialUser);

        Auth::login($user, true);
        $request->session()->regenerate();

        if (! $user->hasVerifiedEmail()) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        return redirect()->to($intended);
    }

    private function callbackUrl(): string
    {
        return route('auth.google.callback');
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
