<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\PrintCVRequest;
use App\Http\Requests\Api\StoreCVRequest;
use App\Http\Requests\Api\UpdateCVRequest;
use App\Models\AtsCheck;
use App\Models\Profile;
use App\Models\Template;
use App\Repositories\CVRepositoryInterface;
use App\Services\AnonymousUserService;
use App\Services\CVDataMapper;
use App\Services\CVPDFService;
use App\Services\CvPhotoService;
use App\Services\TrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CVController extends BaseApiController
{
    public function __construct(
        private CVRepositoryInterface $cvRepository,
        private CVDataMapper $dataMapper,
        private CVPDFService $pdfService,
        private TrackingService $trackingService,
        private CvPhotoService $photoService,
        private AnonymousUserService $anonymousUserService
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $profiles = $this->cvRepository->getAllForUser(
            $user->id,
            $request->input('language')
        );

        $profileIds = $profiles->pluck('id');
        $latestAtsByProfile = $profileIds->isEmpty()
            ? collect()
            : AtsCheck::query()
                ->whereIn('profile_id', $profileIds)
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->get(['profile_id', 'score', 'grade', 'created_at'])
                ->unique('profile_id')
                ->keyBy('profile_id');

        $cvs = $profiles->map(function ($profile) use ($latestAtsByProfile) {
            $data = $this->dataMapper->formatProfileResponse($profile);
            $check = $latestAtsByProfile->get($profile->id);
            $data['latest_ats_score'] = $check?->score !== null ? (int) $check->score : null;
            $data['latest_ats_grade'] = $check?->grade;

            return $data;
        });

        return $this->successResponse($cvs, __('messages.cvs_retrieved'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCVRequest $request)
    {
        $validated = $request->validated();

        // Public routes still accept Bearer tokens — resolve sanctum when present.
        $user = $request->user() ?? $request->user('sanctum');

        // If unauthenticated user provides template_id, generate PDF instead of creating profile
        if (! $user && $request->has('template_id')) {
            return $this->generatePdfFromRequest($request);
        }

        // Authenticated callers always own the CV; guests may still pass user_id.
        $userId = $user?->id ?? $request->input('user_id');
        $anonymousUser = $user ? null : $this->anonymousUserService->resolve($request);
        $clientRef = $this->anonymousUserService->clientRef($request);

        // Guest upsert: same install + client_ref updates existing row.
        if (! $user && $anonymousUser) {
            $existing = $this->anonymousUserService->findGuestProfile(
                $anonymousUser,
                null,
                $clientRef
            );

            if ($existing) {
                return $this->updateGuestProfile($request, $existing, $validated);
            }
        }

        // Map user_data to Profile structure
        $userData = $this->photoService->processUserDataPhoto($request->input('user_data', []));
        $mappedData = $this->dataMapper->mapUserDataToProfile($userData);

        if ($user && $this->isUserDataEmpty($userData)) {
            $mappedData['info'] = [
                'firstName' => $user->first_name,
                'lastName' => $user->last_name,
                'email' => $user->email,
            ];
        }

        $tracking = $this->trackingService->capture($request);

        $templateId = $validated['template_id']
            ?? Template::query()
                ->where('is_active', true)
                ->where('is_default', true)
                ->value('id');

        $ownership = [];
        if ($user) {
            // Authenticated creates may store the install id for attribution.
            $authAnonymous = $this->anonymousUserService->resolve($request);
            if ($authAnonymous) {
                $ownership['anonymous_user_id'] = $authAnonymous->id;
                if ($clientRef) {
                    $ownership['client_ref'] = $clientRef;
                }
            }
        } elseif ($anonymousUser) {
            $ownership = $this->anonymousUserService->ownershipAttributes($request, $anonymousUser);
        }

        $profile = $this->cvRepository->create(array_merge([
            'user_id' => $userId,
            'name' => $validated['name'],
            'language' => $validated['language'] ?? 'en',
            'template_id' => $templateId,
            'is_public' => false,
            'sections_order' => $validated['sections_order'] ?? null,
            'info' => $mappedData['info'] ?? null,
            'interests' => $mappedData['interests'] ?? null,
            'languages' => $mappedData['languages'] ?? null,
            'experiences' => $mappedData['experiences'] ?? null,
            'projects' => $mappedData['projects'] ?? null,
            'educations' => $mappedData['educations'] ?? null,
        ], $tracking, $ownership));

        return $this->successResponse(
            $this->dataMapper->formatProfileResponse($profile),
            __('messages.cv_created'),
            201
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $user = $request->user();

        $profile = $this->cvRepository->findByIdForUser($id, $user->id);

        if (! $profile) {
            return $this->errorResponse(__('messages.cv_not_found'), 404);
        }

        return $this->successResponse(
            $this->dataMapper->formatProfileResponse($profile),
            __('messages.cv_retrieved')
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCVRequest $request, string $id)
    {
        $user = $request->user() ?? $request->user('sanctum');
        $profile = $this->resolveOwnedProfile($request, (int) $id, $user);

        if (! $profile) {
            return $this->errorResponse(__('messages.cv_not_found'), 404);
        }

        $validated = $request->validated();

        // Build update data
        $updateData = $this->trackingService->capture($request);

        if (isset($validated['name'])) {
            $updateData['name'] = $validated['name'];
        }

        if (isset($validated['language'])) {
            $updateData['language'] = $validated['language'];
        }

        if (isset($validated['sections_order'])) {
            $updateData['sections_order'] = $validated['sections_order'];
        }

        if (array_key_exists('template_id', $validated)) {
            $updateData['template_id'] = $validated['template_id'];
        }

        if (array_key_exists('is_public', $validated)) {
            $updateData['is_public'] = $validated['is_public'];
        }

        // Handle user_data updates
        if (isset($validated['user_data'])) {
            $userData = $this->photoService->processUserDataPhoto($validated['user_data']);
            $mappedData = $this->dataMapper->mapUserDataToProfile($userData);

            if (isset($mappedData['info'])) {
                $existingInfo = $profile->info ?? [];
                $updateData['info'] = array_merge($existingInfo, $mappedData['info']);
            }

            if (isset($mappedData['interests'])) {
                $updateData['interests'] = $mappedData['interests'];
            }

            if (isset($mappedData['languages'])) {
                $updateData['languages'] = $mappedData['languages'];
            }

            if (isset($mappedData['experiences'])) {
                $updateData['experiences'] = $mappedData['experiences'];
            }

            if (isset($mappedData['projects'])) {
                $updateData['projects'] = $mappedData['projects'];
            }

            if (isset($mappedData['educations'])) {
                $updateData['educations'] = $mappedData['educations'];
            }
        }

        $updatedProfile = $this->cvRepository->update($profile, $updateData);

        return $this->successResponse(
            $this->dataMapper->formatProfileResponse($updatedProfile),
            __('messages.cv_updated')
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $user = $request->user();

        $profile = $this->cvRepository->findByIdForUser($id, $user->id);

        if (! $profile) {
            return $this->errorResponse(__('messages.cv_not_found'), 404);
        }

        $this->cvRepository->update($profile, $this->trackingService->capture($request));
        $this->cvRepository->delete($profile);

        return $this->successResponse(null, __('messages.cv_deleted'));
    }

    /**
     * Duplicate an owned CV.
     */
    public function duplicate(Request $request, string $id)
    {
        $user = $request->user();

        $original = $this->cvRepository->findByIdForUser($id, $user->id);

        if (! $original) {
            return $this->errorResponse(__('messages.cv_not_found'), 404);
        }

        $copy = $this->cvRepository->create(array_merge([
            'user_id' => $user->id,
            'name' => $this->nextCopyName((string) $original->name),
            'language' => $original->language,
            'template_id' => $original->template_id,
            'is_public' => false,
            'sections_order' => $original->sections_order,
            'info' => $original->info,
            'experiences' => $original->experiences,
            'educations' => $original->educations,
            'projects' => $original->projects,
            'interests' => $original->interests,
            'languages' => $original->languages,
        ], $this->trackingService->capture($request)));

        return $this->successResponse(
            $this->dataMapper->formatProfileResponse($copy),
            __('messages.cv_duplicated'),
            201
        );
    }

    /**
     * Generate PDF from CV data.
     */
    public function print(PrintCVRequest $request)
    {
        $shouldReturnUrl = true;
        $templateId = $request->input('template_id');
        $profileId = $request->input('profile_id');

        $template = $this->cvRepository->findActiveTemplate($templateId);

        if (! $template) {
            return $this->errorResponse(__('messages.template_not_found_or_inactive'), 404);
        }

        $user = $request->user() ?? $request->user('sanctum');
        $anonymousUser = $user ? null : $this->anonymousUserService->resolve($request);
        $clientRef = $this->anonymousUserService->clientRef($request);

        if ($profileId) {
            $profile = $this->cvRepository->findById($profileId);

            if ($user && $profile && $profile->user_id !== $user->id) {
                return $this->errorResponse(__('messages.cv_not_found'), 404);
            }

            if (! $user && $profile) {
                $ownsGuest = $anonymousUser
                    && $profile->user_id === null
                    && $profile->anonymous_user_id === $anonymousUser->id;

                if (! $ownsGuest) {
                    return $this->errorResponse(__('messages.cv_not_found'), 404);
                }
            }

            if (! $profile) {
                return $this->errorResponse(__('messages.cv_not_found'), 404);
            }

            $updateData = $this->trackingService->capture($request);
            if ($request->filled('user_data')) {
                $updateData = array_merge($updateData, $this->mappedProfileFields($request));
            }
            if ($request->filled('name')) {
                $updateData['name'] = $request->input('name');
            }
            if ($request->filled('language')) {
                $updateData['language'] = $request->input('language');
            }
            if ($request->has('sections_order')) {
                $updateData['sections_order'] = $request->input('sections_order');
            }

            $this->cvRepository->update($profile, $updateData);
        } else {
            $profile = null;

            if (! $user && $anonymousUser) {
                $profile = $this->anonymousUserService->findGuestProfile(
                    $anonymousUser,
                    null,
                    $clientRef
                );
            }

            if ($profile) {
                $updateData = array_merge(
                    $this->trackingService->capture($request),
                    $this->mappedProfileFields($request)
                );
                if ($request->filled('name')) {
                    $updateData['name'] = $request->input('name');
                }
                if ($request->filled('language')) {
                    $updateData['language'] = $request->input('language');
                }
                if ($request->has('sections_order')) {
                    $updateData['sections_order'] = $request->input('sections_order');
                }
                $this->cvRepository->update($profile, $updateData);
            } else {
                $ownership = [];
                if ($user) {
                    $authAnonymous = $this->anonymousUserService->resolve($request);
                    if ($authAnonymous) {
                        $ownership['anonymous_user_id'] = $authAnonymous->id;
                        if ($clientRef) {
                            $ownership['client_ref'] = $clientRef;
                        }
                    }
                } elseif ($anonymousUser) {
                    $ownership = $this->anonymousUserService->ownershipAttributes($request, $anonymousUser);
                }

                $profile = $this->pdfService->createTemporaryProfile(
                    $this->photoService->processUserDataPhoto($request->input('user_data', [])),
                    $user?->id,
                    $request->input('name', 'CV'),
                    $request->input('language', 'en'),
                    $request->input('sections_order')
                );

                $profile->fill(array_merge(
                    $this->trackingService->capture($request),
                    $ownership
                ));
                $profile->save();
            }
        }

        return $this->respondWithPdf($profile, $template, $shouldReturnUrl);
    }

    /**
     * Generate PDF from request data (used in store method for unauthenticated users).
     */
    private function generatePdfFromRequest(Request $request)
    {
        $shouldReturnUrl = true;
        $templateId = $request->input('template_id');

        $template = $this->cvRepository->findActiveTemplate($templateId);

        if (! $template) {
            return $this->errorResponse(__('messages.template_not_found_or_inactive'), 404);
        }

        $anonymousUser = $this->anonymousUserService->resolve($request);
        $clientRef = $this->anonymousUserService->clientRef($request);
        $profile = null;

        if ($anonymousUser) {
            $profile = $this->anonymousUserService->findGuestProfile(
                $anonymousUser,
                $request->integer('profile_id') ?: null,
                $clientRef
            );
        }

        if ($profile) {
            $updateData = array_merge(
                $this->trackingService->capture($request),
                $this->mappedProfileFields($request)
            );
            if ($request->filled('name')) {
                $updateData['name'] = $request->input('name');
            }
            if ($request->filled('language')) {
                $updateData['language'] = $request->input('language');
            }
            if ($request->has('sections_order')) {
                $updateData['sections_order'] = $request->input('sections_order');
            }
            $this->cvRepository->update($profile, $updateData);
        } else {
            $ownership = $anonymousUser
                ? $this->anonymousUserService->ownershipAttributes($request, $anonymousUser)
                : [];

            $profile = $this->pdfService->createTemporaryProfile(
                $this->photoService->processUserDataPhoto($request->input('user_data', [])),
                null,
                $request->input('name', 'CV'),
                $request->input('language', 'en'),
                $request->input('sections_order')
            );

            $profile->fill(array_merge(
                $this->trackingService->capture($request),
                $ownership
            ));
            $profile->save();
        }

        return $this->respondWithPdf($profile, $template, $shouldReturnUrl);
    }

    private function updateGuestProfile(Request $request, Profile $profile, array $validated)
    {
        $updateData = $this->trackingService->capture($request);

        if (isset($validated['name'])) {
            $updateData['name'] = $validated['name'];
        }
        if (isset($validated['language'])) {
            $updateData['language'] = $validated['language'];
        }
        if (isset($validated['sections_order'])) {
            $updateData['sections_order'] = $validated['sections_order'];
        }
        if (array_key_exists('template_id', $validated)) {
            $updateData['template_id'] = $validated['template_id'];
        }
        if ($request->filled('user_data')) {
            $updateData = array_merge($updateData, $this->mappedProfileFields($request));
        }

        $updated = $this->cvRepository->update($profile, $updateData);

        return $this->successResponse(
            $this->dataMapper->formatProfileResponse($updated),
            __('messages.cv_updated')
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function mappedProfileFields(Request $request): array
    {
        $userData = $this->photoService->processUserDataPhoto($request->input('user_data', []));
        $mappedData = $this->dataMapper->mapUserDataToProfile($userData);

        return array_filter([
            'info' => $mappedData['info'] ?? null,
            'interests' => $mappedData['interests'] ?? null,
            'languages' => $mappedData['languages'] ?? null,
            'experiences' => $mappedData['experiences'] ?? null,
            'projects' => $mappedData['projects'] ?? null,
            'educations' => $mappedData['educations'] ?? null,
        ], fn ($value) => $value !== null);
    }

    private function resolveOwnedProfile(Request $request, int $id, $user): ?Profile
    {
        if ($user) {
            return $this->cvRepository->findByIdForUser($id, $user->id);
        }

        $anonymousUser = $this->anonymousUserService->resolve($request);

        if (! $anonymousUser) {
            return null;
        }

        return $this->anonymousUserService->findGuestProfile($anonymousUser, $id);
    }

    private function respondWithPdf(Profile $profile, Template $template, bool $shouldReturnUrl)
    {
        try {
            if ($shouldReturnUrl) {
                $url = $this->pdfService->generatePdf($profile, $template, true);

                return $this->successResponse(
                    [
                        'url' => $url,
                        'profile_id' => $profile->id,
                    ],
                    __('messages.pdf_generated_successfully')
                );
            }

            return $this->pdfService->generatePdf($profile, $template);
        } catch (\RuntimeException $e) {
            return $this->errorResponse(__('messages.pdf_generation_failed').': '.$e->getMessage(), 500);
        } catch (\Exception $e) {
            $errorMessage = __('messages.pdf_generation_failed');
            if (app()->environment('production')) {
                $errorMessage .= ' '.__('messages.contact_support');
            } else {
                $errorMessage .= ': '.$e->getMessage();
            }

            return $this->errorResponse($errorMessage, 500);
        }
    }

    private function isUserDataEmpty(array $userData): bool
    {
        foreach ($userData as $value) {
            if (is_array($value) && $value !== []) {
                return false;
            }
            if (is_string($value) && trim($value) !== '') {
                return false;
            }
            if ($value !== null && $value !== '' && $value !== []) {
                return false;
            }
        }

        return true;
    }

    private function nextCopyName(string $original): string
    {
        if (Str::contains($original, ' (Copy')) {
            return preg_replace('/\(Copy( \d+)?\)$/', '(Copy '.((int) (Str::afterLast($original, ' ')) + 1 ?: 2).')', $original)
                ?: $original.' (Copy)';
        }

        return $original.' (Copy)';
    }
}
