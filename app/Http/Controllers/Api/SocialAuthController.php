<?php

namespace App\Http\Controllers\Api;

use App\Services\Auth\SocialAccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class SocialAuthController extends BaseApiController
{
    public function __construct(private readonly SocialAccountService $socialAccounts)
    {
    }

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

        $user = $this->socialAccounts->findOrCreateUser('google', $socialUser);

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

    public function redirect(string $provider): JsonResponse
    {
        if (! in_array($provider, ['google', 'linkedin'], true)) {
            return $this->errorResponse(__('messages.invalid_provider'), 400);
        }

        $url = Socialite::driver($provider)->stateless()->redirect()->getTargetUrl();

        return $this->successResponse(['url' => $url], __('messages.social_redirect_url'));
    }

    public function callback(Request $request, string $provider): JsonResponse
    {
        if (! in_array($provider, ['google', 'linkedin'], true)) {
            return $this->errorResponse(__('messages.invalid_provider'), 400);
        }

        try {
            $socialUser = Socialite::driver($provider)->stateless()->user();
        } catch (Throwable $e) {
            return $this->errorResponse(__('messages.social_auth_failed'), 401);
        }

        $user = $this->socialAccounts->findOrCreateUser($provider, $socialUser);

        $token = $user->createToken('social-token')->plainTextToken;

        return $this->successResponse([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'type' => $user->type->value,
                'active' => $user->active,
            ],
        ], __('messages.login_successful'));
    }
}
