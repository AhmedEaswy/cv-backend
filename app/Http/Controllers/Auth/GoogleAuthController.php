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
        $request->session()->put('url.intended', $request->query('return_to', route('portal.dashboard')));

        return Socialite::driver('google')
            ->stateless()
            ->redirectUrl(route('auth.google.callback'))
            ->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        $intended = (string) $request->session()->pull('url.intended', route('portal.dashboard'));

        try {
            $socialUser = Socialite::driver('google')
                ->stateless()
                ->user();
        } catch (Throwable $e) {
            Log::warning('Google social auth failed', ['error' => $e->getMessage()]);

            return redirect()->route('login')
                ->withErrors(['email' => __('messages.social_auth_failed')]);
        }

        $user = $this->socialAccounts->findOrCreateUser('google', $socialUser);

        Auth::login($user, true);
        $request->session()->regenerate();

        if (! $user->hasVerifiedEmail()) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        return redirect()->to($intended)
            ->with('status', __('messages.welcome_back', ['name' => $user->full_name]));
    }
}
