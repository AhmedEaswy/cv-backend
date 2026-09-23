<?php

namespace App\Http\Controllers\Api;

use App\Services\Auth\SocialAccountService;
use App\Services\CVDataMapper;
use App\Services\LinkedIn\LinkedInCvImporter;
use App\Support\SocialProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class SocialAuthController extends BaseApiController
{
    public function __construct(
        private readonly SocialAccountService $socialAccounts,
        private readonly LinkedInCvImporter $linkedinImporter,
        private readonly CVDataMapper $cvMapper,
    ) {}

    /**
     * Exchange a Google OAuth access token for a Sanctum API token.
     *
     * Body field is named `code` for parity with digi-pedia, but the value is
     * the Google access_token from Auth.js (not an authorization code).
     */
    public function google(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        try {
            $socialUser = Socialite::driver('google')
                ->stateless()
                ->userFromToken($request->string('code')->toString());
        } catch (Throwable $e) {
            Log::warning('Google token auth failed', ['error' => $e->getMessage()]);

            return $this->errorResponse(__('messages.social_auth_failed'), 401);
        }

        $user = $this->socialAccounts->findOrCreateUser(SocialProvider::GOOGLE, $socialUser);

        if (! $user->isActive()) {
            return $this->errorResponse(__('messages.account_inactive'), 403);
        }

        $token = $user->createToken('google_auth')->plainTextToken;

        return $this->successResponse([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ], __('messages.login_success'));
    }

    /**
     * Exchange a LinkedIn OpenID access token for a Sanctum API token.
     * Optionally creates a CV from the LinkedIn profile (default: when the user has none).
     */
    public function linkedin(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string',
            'import_cv' => 'sometimes|boolean',
        ]);

        $accessToken = $request->string('code')->toString();

        try {
            $socialUser = Socialite::driver(SocialProvider::socialiteDriver(SocialProvider::LINKEDIN))
                ->stateless()
                ->userFromToken($accessToken);
        } catch (Throwable $e) {
            Log::warning('LinkedIn token auth failed', ['error' => $e->getMessage()]);

            return $this->errorResponse(__('messages.social_auth_failed'), 401);
        }

        $user = $this->socialAccounts->findOrCreateUser(SocialProvider::LINKEDIN, $socialUser);

        if (! $user->isActive()) {
            return $this->errorResponse(__('messages.account_inactive'), 403);
        }

        $token = $user->createToken('linkedin_auth')->plainTextToken;
        $importCv = $request->has('import_cv')
            ? $request->boolean('import_cv')
            : $this->linkedinImporter->shouldAutoImport($user);

        $cv = null;
        if ($importCv) {
            try {
                $cv = $this->linkedinImporter->importForUser(
                    $user,
                    $accessToken,
                    method_exists($socialUser, 'getRaw') ? ($socialUser->getRaw() ?? []) : [],
                    $request
                );
            } catch (Throwable $e) {
                Log::warning('LinkedIn CV import failed', ['error' => $e->getMessage()]);
            }
        }

        $payload = [
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ];

        if ($cv) {
            $payload['cv'] = $this->cvMapper->formatProfileResponse($cv);
        }

        return $this->successResponse($payload, __('messages.login_success'));
    }

    /**
     * Exchange an Apple identity token for a Sanctum API token.
     *
     * Body field is named `code` for parity with Google/LinkedIn, but the value is
     * the Apple identity token (JWT) from native Sign in with Apple.
     * Optional `name` / `first_name` / `last_name` — Apple only returns the name
     * on the first authorization.
     * Optional `nonce` — verified against the identity token when provided.
     */
    public function apple(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string',
            'nonce' => 'sometimes|nullable|string',
            'name' => 'sometimes|nullable|string|max:255',
            'first_name' => 'sometimes|nullable|string|max:255',
            'last_name' => 'sometimes|nullable|string|max:255',
        ]);

        $identityToken = $request->string('code')->toString();
        $nonce = $request->filled('nonce') ? $request->string('nonce')->toString() : null;

        try {
            $driver = $this->appleDriverForIdentityToken();
            $socialUser = $driver->userByIdentityToken($identityToken, $nonce);
        } catch (Throwable $e) {
            Log::warning('Apple token auth failed', ['error' => $e->getMessage()]);

            return $this->errorResponse(__('messages.social_auth_failed'), 401);
        }

        $this->applyOptionalAppleName($socialUser, $request);

        $user = $this->socialAccounts->findOrCreateUser(SocialProvider::APPLE, $socialUser);

        if (! $user->isActive()) {
            return $this->errorResponse(__('messages.account_inactive'), 403);
        }

        $token = $user->createToken('apple_auth')->plainTextToken;

        return $this->successResponse([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ], __('messages.login_success'));
    }

    public function redirect(string $provider): JsonResponse
    {
        if (! SocialProvider::isSupported($provider)) {
            return $this->errorResponse(__('messages.invalid_provider'), 400);
        }

        $driver = Socialite::driver(SocialProvider::socialiteDriver($provider))->stateless();

        if ($provider === SocialProvider::LINKEDIN) {
            $driver->redirectUrl(url('/api/v1/auth/linkedin/callback'));
            if (config('services.linkedin.dma_enabled') && method_exists($driver, 'scopes')) {
                $driver->scopes(['r_dma_portability_3rd_party']);
            }
        }

        if ($provider === SocialProvider::APPLE) {
            $driver->redirectUrl(url('/api/v1/auth/apple/callback'));
            if (method_exists($driver, 'cookieNonce')) {
                $driver->cookieNonce();
            }
        }

        $url = $driver->redirect()->getTargetUrl();

        return $this->successResponse(['url' => $url], __('messages.social_redirect_url'));
    }

    public function callback(Request $request, string $provider): JsonResponse
    {
        if (! SocialProvider::isSupported($provider)) {
            return $this->errorResponse(__('messages.invalid_provider'), 400);
        }

        try {
            $driver = Socialite::driver(SocialProvider::socialiteDriver($provider))->stateless();
            if ($provider === SocialProvider::LINKEDIN) {
                $driver->redirectUrl(url('/api/v1/auth/linkedin/callback'));
            }
            if ($provider === SocialProvider::APPLE) {
                $driver->redirectUrl(url('/api/v1/auth/apple/callback'));
                if (method_exists($driver, 'cookieNonce')) {
                    $driver->cookieNonce();
                }
            }
            $socialUser = $driver->user();
        } catch (Throwable $e) {
            return $this->errorResponse(__('messages.social_auth_failed'), 401);
        }

        $user = $this->socialAccounts->findOrCreateUser($provider, $socialUser);

        $token = $user->createToken('social-token')->plainTextToken;
        $payload = [
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'type' => $user->type->value,
                'active' => $user->active,
            ],
        ];

        if ($provider === SocialProvider::LINKEDIN) {
            $accessToken = is_string($socialUser->token) ? $socialUser->token : '';
            if ($accessToken !== '' && $this->linkedinImporter->shouldAutoImport($user)) {
                try {
                    $cv = $this->linkedinImporter->importForUser(
                        $user,
                        $accessToken,
                        method_exists($socialUser, 'getRaw') ? ($socialUser->getRaw() ?? []) : [],
                        $request
                    );
                    $payload['cv'] = $this->cvMapper->formatProfileResponse($cv);
                } catch (Throwable $e) {
                    Log::warning('LinkedIn CV import failed', ['error' => $e->getMessage()]);
                }
            }
        }

        return $this->successResponse($payload, __('messages.login_successful'));
    }

    /**
     * Native identity tokens use the Bundle ID as `aud`; web uses the Services ID.
     *
     * @return \SocialiteProviders\Apple\Provider
     */
    private function appleDriverForIdentityToken()
    {
        $nativeClientId = config('services.apple.native_client_id');
        if (is_string($nativeClientId) && $nativeClientId !== '') {
            config(['services.apple.client_id' => $nativeClientId]);
        }

        /** @var \SocialiteProviders\Apple\Provider $driver */
        $driver = Socialite::driver(SocialProvider::APPLE)->stateless();

        return $driver;
    }

    private function applyOptionalAppleName(object $socialUser, Request $request): void
    {
        $first = trim((string) $request->input('first_name', ''));
        $last = trim((string) $request->input('last_name', ''));
        $full = trim((string) $request->input('name', ''));

        if ($full === '' && ($first !== '' || $last !== '')) {
            $full = trim($first.' '.$last);
        }

        if ($full === '') {
            return;
        }

        if (method_exists($socialUser, 'setRaw')) {
            $raw = method_exists($socialUser, 'getRaw') ? ($socialUser->getRaw() ?? []) : [];
            $raw['given_name'] = $first !== '' ? $first : ($raw['given_name'] ?? null);
            $raw['family_name'] = $last !== '' ? $last : ($raw['family_name'] ?? null);
            if ($first !== '' || $last !== '') {
                $raw['name'] = [
                    'firstName' => $first,
                    'lastName' => $last,
                ];
            }
            $socialUser->setRaw($raw);
        }

        if (property_exists($socialUser, 'name')) {
            $socialUser->name = $full;
        }
    }
}
