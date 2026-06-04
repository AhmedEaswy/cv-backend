<?php

namespace App\Http\Controllers\Api;

use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends BaseApiController
{
    public function redirect(string $provider): JsonResponse
    {
        if (! in_array($provider, ['google', 'linkedin'])) {
            return $this->errorResponse(__('messages.invalid_provider'), 400);
        }

        $url = Socialite::driver($provider)->stateless()->redirect()->getTargetUrl();

        return $this->successResponse(['url' => $url], __('messages.social_redirect_url'));
    }

    public function callback(Request $request, string $provider): JsonResponse
    {
        if (! in_array($provider, ['google', 'linkedin'])) {
            return $this->errorResponse(__('messages.invalid_provider'), 400);
        }

        try {
            $socialUser = Socialite::driver($provider)->stateless()->user();
        } catch (\Exception $e) {
            return $this->errorResponse(__('messages.social_auth_failed'), 401);
        }

        $socialAccount = SocialAccount::where('provider_name', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();

        if ($socialAccount) {
            $user = $socialAccount->user;
        } else {
            $user = User::where('email', $socialUser->getEmail())->first();

            if (! $user) {
                $name = $socialUser->getName() ?? $socialUser->getEmail();
                $nameParts = explode(' ', $name, 2);

                $user = User::create([
                    'name' => $name,
                    'first_name' => $nameParts[0] ?? $name,
                    'last_name' => $nameParts[1] ?? '',
                    'email' => $socialUser->getEmail(),
                    'password' => Hash::make(Str::random(32)),
                    'type' => 'user',
                    'active' => true,
                ]);
            }

            SocialAccount::create([
                'user_id' => $user->id,
                'provider_name' => $provider,
                'provider_id' => $socialUser->getId(),
                'provider_token' => $socialUser->token,
                'provider_refresh_token' => $socialUser->refreshToken,
            ]);
        }

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
