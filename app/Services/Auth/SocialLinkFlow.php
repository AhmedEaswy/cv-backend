<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUserContract;

/**
 * "Link a provider" flow for already signed-in portal users.
 *
 * The Nuxt portal authenticates with a Sanctum bearer token, which a full-page
 * OAuth redirect can't carry. So the API issues a short-lived one-time token,
 * the web redirect stores it in a cookie, and the provider callback redeems it
 * to find which user to attach the account to (instead of logging someone in).
 */
class SocialLinkFlow
{
    private const COOKIE = 'social_link';

    private const TTL_MINUTES = 10;

    public function __construct(private readonly SocialAccountService $socialAccounts) {}

    public function issueToken(User $user): string
    {
        $token = Str::random(64);
        Cache::put($this->cacheKey($token), $user->id, now()->addMinutes(self::TTL_MINUTES));

        return $token;
    }

    /**
     * Call from the provider redirect step. Apple posts the callback cross-site,
     * so the cookie needs SameSite=None (which in turn requires Secure).
     */
    public function rememberFromRequest(Request $request): void
    {
        $token = $request->query('link_token');
        if (! is_string($token) || $token === '' || ! Cache::has($this->cacheKey($token))) {
            Cookie::queue(Cookie::forget(self::COOKIE));

            return;
        }

        $secure = $request->isSecure() || (bool) config('session.secure');

        Cookie::queue(Cookie::make(
            self::COOKIE,
            $token,
            self::TTL_MINUTES,
            '/',
            null,
            $secure,
            true,
            false,
            $secure ? 'none' : 'lax',
        ));
    }

    /**
     * Returns a redirect when this callback belongs to a link flow, or null
     * when it's a normal sign-in and the caller should continue as usual.
     */
    public function completeIfLinking(Request $request, string $provider, SocialiteUserContract $socialUser): ?RedirectResponse
    {
        $user = $this->pullLinkingUser($request);
        if (! $user) {
            return null;
        }

        try {
            $this->socialAccounts->linkToUser($user, $provider, $socialUser);
        } catch (SocialLinkException $e) {
            return redirect()->to($this->settingsUrl(['link_error' => $e->reason, 'provider' => $provider]));
        }

        return redirect()->to($this->settingsUrl(['linked' => $provider]));
    }

    /**
     * Where to send the user when the provider itself fails during a link flow.
     */
    public function failureRedirect(Request $request, string $provider): ?RedirectResponse
    {
        if (! $this->pullLinkingUser($request)) {
            return null;
        }

        return redirect()->to($this->settingsUrl(['link_error' => 'failed', 'provider' => $provider]));
    }

    private function pullLinkingUser(Request $request): ?User
    {
        $token = $request->cookie(self::COOKIE);
        if (! is_string($token) || $token === '') {
            return null;
        }

        Cookie::queue(Cookie::forget(self::COOKIE));
        $userId = Cache::pull($this->cacheKey($token));

        return $userId ? User::find($userId) : null;
    }

    /**
     * @param  array<string, string>  $query
     */
    private function settingsUrl(array $query): string
    {
        $base = rtrim((string) config('app.frontend_url'), '/').'/portal/settings';

        return $base.'?'.http_build_query($query);
    }

    private function cacheKey(string $token): string
    {
        return 'social_link:'.hash('sha256', $token);
    }
}
