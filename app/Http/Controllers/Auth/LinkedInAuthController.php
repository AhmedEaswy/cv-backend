<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\SocialAccountService;
use App\Services\LinkedIn\LinkedInCvImporter;
use App\Support\SocialProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Laravel\Socialite\Contracts\Provider as SocialiteProvider;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class LinkedInAuthController extends Controller
{
    public function __construct(
        private readonly SocialAccountService $socialAccounts,
        private readonly LinkedInCvImporter $importer,
    ) {}

    public function redirect(Request $request): RedirectResponse
    {
        if (! config('services.linkedin-openid.client_id')) {
            return redirect()->to($this->frontendUrl('/auth/login?error=social'));
        }

        $request->session()->put(
            'url.intended',
            $this->safeReturnTo($request->query('return_to'))
        );
        $request->session()->put(
            'linkedin.intent',
            $request->query('intent') === 'import' ? 'import' : 'login'
        );

        return $this->driver()->redirect();
    }

    public function callback(Request $request): RedirectResponse|View
    {
        $intended = $this->safeReturnTo(
            $request->session()->pull('url.intended')
        );
        $intent = $request->session()->pull('linkedin.intent', 'login');

        try {
            $socialUser = $this->driver()->user();
        } catch (Throwable $e) {
            Log::warning('LinkedIn social auth failed', ['error' => $e->getMessage()]);

            return redirect()->to($this->frontendUrl('/auth/login?error=social'));
        }

        $user = $this->socialAccounts->findOrCreateUser(SocialProvider::LINKEDIN, $socialUser);

        Auth::login($user, true);
        $request->session()->regenerate();

        if (! $user->hasVerifiedEmail()) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        $token = $user->createToken('linkedin_auth')->plainTextToken;

        $shouldImport = $intent === 'import' || $this->importer->shouldAutoImport($user);
        $cv = null;
        $accessToken = is_string($socialUser->token) ? $socialUser->token : '';
        if ($shouldImport && $accessToken !== '') {
            try {
                $cv = $this->importer->importForUser(
                    $user,
                    $accessToken,
                    method_exists($socialUser, 'getRaw') ? ($socialUser->getRaw() ?? []) : [],
                    $request
                );
            } catch (Throwable $e) {
                Log::warning('LinkedIn CV import failed', ['error' => $e->getMessage()]);
            }
        }

        $redirect = $cv
            ? $this->frontendUrl('/portal/cvs/'.$cv->id.'/edit')
            : $intended;

        return view('auth.google-handoff', [
            'token' => $token,
            'redirect' => $redirect,
        ]);
    }

    private function driver(): SocialiteProvider
    {
        $driver = Socialite::driver(SocialProvider::socialiteDriver(SocialProvider::LINKEDIN))
            ->stateless()
            ->redirectUrl($this->callbackUrl());

        if (config('services.linkedin.dma_enabled') && method_exists($driver, 'scopes')) {
            $driver->scopes(['r_dma_portability_3rd_party']);
        }

        return $driver;
    }

    private function callbackUrl(): string
    {
        return route('auth.linkedin.callback');
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
