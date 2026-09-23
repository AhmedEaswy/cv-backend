<?php

namespace App\Http\Controllers\Api;

use App\Services\CVDataMapper;
use App\Services\LinkedIn\LinkedInCvImporter;
use App\Support\SocialProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class LinkedInCvController extends BaseApiController
{
    public function __construct(
        private readonly LinkedInCvImporter $importer,
        private readonly CVDataMapper $mapper,
    ) {}

    /**
     * Create a CV from the authenticated user's stored LinkedIn token,
     * or from a fresh access token in `code`.
     */
    public function store(Request $request): JsonResponse
    {
        if (! SocialProvider::isEnabled(SocialProvider::LINKEDIN)) {
            return $this->errorResponse(__('messages.invalid_provider'), 403);
        }

        $request->validate([
            'code' => 'sometimes|string',
        ]);

        $user = $request->user();
        $accessToken = $request->string('code')->toString();

        if ($accessToken === '') {
            $accessToken = (string) $user->socialAccounts()
                ->where('provider_name', SocialProvider::LINKEDIN)
                ->value('provider_token');
        }

        if ($accessToken === '') {
            return $this->errorResponse(__('messages.linkedin_reconnect'), 409);
        }

        try {
            $profile = $this->importer->importForUser($user, $accessToken, [], $request);
        } catch (Throwable $e) {
            Log::warning('LinkedIn CV import failed', ['error' => $e->getMessage()]);

            return $this->errorResponse(__('messages.linkedin_cv_import_failed'), 422);
        }

        return $this->successResponse(
            $this->mapper->formatProfileResponse($profile),
            __('messages.linkedin_cv_imported'),
            201
        );
    }
}
