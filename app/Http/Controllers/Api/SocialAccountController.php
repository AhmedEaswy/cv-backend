<?php

namespace App\Http\Controllers\Api;

use App\Services\Auth\SocialAccountService;
use App\Services\Auth\SocialLinkFlow;
use App\Support\SocialProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SocialAccountController extends BaseApiController
{
    public function __construct(
        private readonly SocialAccountService $socialAccounts,
        private readonly SocialLinkFlow $linkFlow,
    ) {}

    /**
     * Every supported provider with whether it's linked to the current user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $linkedAccounts = $user->socialAccounts()->get()->keyBy('provider_name');

        $providers = array_map(function (string $provider) use ($user, $linkedAccounts) {
            $account = $linkedAccounts->get($provider);

            return [
                'provider' => $provider,
                'available' => SocialProvider::isAvailable($provider),
                'linked' => $account !== null,
                'linked_at' => $account?->created_at?->toIso8601String(),
                'can_unlink' => $account !== null && $this->socialAccounts->canUnlink($user, $provider),
            ];
        }, SocialProvider::all());

        return $this->successResponse(['providers' => $providers]);
    }

    /**
     * Returns the URL the browser should open to link the provider.
     */
    public function link(Request $request, string $provider): JsonResponse
    {
        if (! SocialProvider::isAvailable($provider)) {
            return $this->errorResponse(__('messages.invalid_provider'), 400);
        }

        $isAlreadyLinked = $request->user()->socialAccounts()->where('provider_name', $provider)->exists();
        if ($isAlreadyLinked) {
            return $this->errorResponse(__('messages.social_already_linked'), 409);
        }

        $query = http_build_query([
            'link_token' => $this->linkFlow->issueToken($request->user()),
            'return_to' => '/portal/settings',
        ]);

        return $this->successResponse([
            'url' => url("/auth/{$provider}/redirect").'?'.$query,
        ]);
    }

    public function destroy(Request $request, string $provider): JsonResponse
    {
        if (! SocialProvider::isSupported($provider)) {
            return $this->errorResponse(__('messages.invalid_provider'), 400);
        }

        $user = $request->user();

        if (! $this->socialAccounts->canUnlink($user, $provider)) {
            return $this->errorResponse(__('messages.social_unlink_last_method'), 422);
        }

        $this->socialAccounts->unlink($user, $provider);

        return $this->successResponse(null, __('messages.social_unlinked'));
    }
}
