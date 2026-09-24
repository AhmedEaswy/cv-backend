<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\PrintCoverLetterRequest;
use App\Http\Requests\Api\StoreCoverLetterRequest;
use App\Http\Requests\Api\UpdateCoverLetterRequest;
use App\Models\CoverLetter;
use App\Models\CoverLetterTemplate;
use App\Repositories\CoverLetterRepository;
use App\Services\AnonymousUserService;
use App\Services\CoverLetterDataMapper;
use App\Services\CoverLetterPDFService;
use App\Services\TrackingService;
use Illuminate\Http\Request;

class CoverLetterController extends BaseApiController
{
    public function __construct(
        private CoverLetterRepository $repository,
        private CoverLetterDataMapper $dataMapper,
        private CoverLetterPDFService $pdfService,
        private TrackingService $trackingService,
        private AnonymousUserService $anonymousUserService
    ) {
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $coverLetters = $this->repository->getAllForUser(
            $user->id,
            $request->input('language')
        );

        $data = $coverLetters->map(fn ($cl) => $this->dataMapper->formatCoverLetterResponse($cl));

        return $this->successResponse($data, __('messages.cover_letters_retrieved'));
    }

    public function store(StoreCoverLetterRequest $request)
    {
        $validated = $request->validated();

        $user = $request->user() ?? $request->user('sanctum');
        $userId = $user?->id ?? $request->input('user_id');
        $anonymousUser = $user ? null : $this->anonymousUserService->resolve($request);
        $clientRef = $this->anonymousUserService->clientRef($request);

        if (! $user && $anonymousUser) {
            $existing = $this->anonymousUserService->findGuestCoverLetter(
                $anonymousUser,
                null,
                $clientRef
            );

            if ($existing) {
                return $this->applyCoverLetterUpdate($request, $existing, $validated);
            }
        }

        $userData = $request->input('user_data', []);
        $mappedData = $this->dataMapper->mapUserDataToCoverLetter($userData);
        $tracking = $this->trackingService->capture($request);

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

        $coverLetter = $this->repository->create(array_merge([
            'user_id' => $userId,
            'name' => $validated['name'],
            'language' => $validated['language'] ?? 'en',
            'sections_order' => $validated['sections_order'] ?? null,
            'cover_letter_template_id' => $validated['cover_letter_template_id'] ?? null,
            'info' => $mappedData['info'] ?? null,
            'experiences' => $mappedData['experiences'] ?? null,
        ], $tracking, $ownership));

        return $this->successResponse(
            $this->dataMapper->formatCoverLetterResponse($coverLetter),
            __('messages.cover_letter_created'),
            201
        );
    }

    public function show(Request $request, string $id)
    {
        $user = $request->user();

        $coverLetter = $this->repository->findByIdForUser($id, $user->id);

        if (! $coverLetter) {
            return $this->errorResponse(__('messages.cover_letter_not_found'), 404);
        }

        return $this->successResponse(
            $this->dataMapper->formatCoverLetterResponse($coverLetter),
            __('messages.cover_letter_retrieved')
        );
    }

    public function update(UpdateCoverLetterRequest $request, string $id)
    {
        $user = $request->user() ?? $request->user('sanctum');
        $coverLetter = $this->resolveOwnedCoverLetter($request, (int) $id, $user);

        if (! $coverLetter) {
            return $this->errorResponse(__('messages.cover_letter_not_found'), 404);
        }

        return $this->applyCoverLetterUpdate($request, $coverLetter, $request->validated());
    }

    public function destroy(Request $request, string $id)
    {
        $user = $request->user();

        $coverLetter = $this->repository->findByIdForUser($id, $user->id);

        if (! $coverLetter) {
            return $this->errorResponse(__('messages.cover_letter_not_found'), 404);
        }

        $this->repository->update($coverLetter, $this->trackingService->capture($request));
        $this->repository->delete($coverLetter);

        return $this->successResponse(null, __('messages.cover_letter_deleted'));
    }

    public function print(PrintCoverLetterRequest $request)
    {
        $shouldReturnUrl = true;
        $templateId = $request->input('template_id');
        $coverLetterId = $request->input('cover_letter_id');

        $template = $this->repository->findActiveTemplate($templateId);

        if (! $template) {
            return $this->errorResponse(__('messages.template_not_found_or_inactive'), 404);
        }

        $user = $request->user() ?? $request->user('sanctum');
        $anonymousUser = $user ? null : $this->anonymousUserService->resolve($request);
        $clientRef = $this->anonymousUserService->clientRef($request);

        if ($coverLetterId) {
            $coverLetter = $this->repository->findById($coverLetterId);

            if ($user && $coverLetter && $coverLetter->user_id !== $user->id) {
                return $this->errorResponse(__('messages.cover_letter_not_found'), 404);
            }

            if (! $user && $coverLetter) {
                $ownsGuest = $anonymousUser
                    && $coverLetter->user_id === null
                    && $coverLetter->anonymous_user_id === $anonymousUser->id;

                if (! $ownsGuest) {
                    return $this->errorResponse(__('messages.cover_letter_not_found'), 404);
                }
            }

            if (! $coverLetter) {
                return $this->errorResponse(__('messages.cover_letter_not_found'), 404);
            }

            $updateData = $this->trackingService->capture($request);
            if ($request->filled('user_data')) {
                $mappedData = $this->dataMapper->mapUserDataToCoverLetter($request->input('user_data', []));
                if (isset($mappedData['info'])) {
                    $updateData['info'] = $mappedData['info'];
                }
                if (isset($mappedData['experiences'])) {
                    $updateData['experiences'] = $mappedData['experiences'];
                }
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

            $this->repository->update($coverLetter, $updateData);
        } else {
            $coverLetter = null;

            if (! $user && $anonymousUser) {
                $coverLetter = $this->anonymousUserService->findGuestCoverLetter(
                    $anonymousUser,
                    null,
                    $clientRef
                );
            }

            if ($coverLetter) {
                $updateData = $this->trackingService->capture($request);
                $mappedData = $this->dataMapper->mapUserDataToCoverLetter($request->input('user_data', []));
                if (isset($mappedData['info'])) {
                    $updateData['info'] = $mappedData['info'];
                }
                if (isset($mappedData['experiences'])) {
                    $updateData['experiences'] = $mappedData['experiences'];
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
                $this->repository->update($coverLetter, $updateData);
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

                $coverLetter = $this->pdfService->createTemporaryCoverLetter(
                    $request->input('user_data', []),
                    $user?->id,
                    $request->input('name', 'Cover Letter'),
                    $request->input('language', 'en'),
                    $request->input('sections_order')
                );

                if (! $coverLetter->exists) {
                    $tracking = $this->trackingService->capture($request);
                    $coverLetter = $this->repository->create(array_merge(
                        $coverLetter->getAttributes(),
                        $tracking,
                        $ownership
                    ));
                }
            }
        }

        return $this->respondWithPdf($coverLetter, $template, $shouldReturnUrl);
    }

    public function templates(Request $request)
    {
        return $this->paginatedOrAll(
            $this->repository->activeTemplatesQuery(),
            $request,
            fn ($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'preview' => $t->resolvedPreviewUrl(app()->getLocale()),
                'description' => $t->description,
                'is_default' => $t->is_default,
            ],
            __('messages.templates_retrieved')
        );
    }

    private function applyCoverLetterUpdate(Request $request, CoverLetter $coverLetter, array $validated)
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
        if (isset($validated['cover_letter_template_id'])) {
            $updateData['cover_letter_template_id'] = $validated['cover_letter_template_id'];
        }
        if (isset($validated['user_data'])) {
            $mappedData = $this->dataMapper->mapUserDataToCoverLetter($validated['user_data']);
            if (isset($mappedData['info'])) {
                $updateData['info'] = $mappedData['info'];
            }
            if (isset($mappedData['experiences'])) {
                $updateData['experiences'] = $mappedData['experiences'];
            }
        }

        $updated = $this->repository->update($coverLetter, $updateData);

        return $this->successResponse(
            $this->dataMapper->formatCoverLetterResponse($updated),
            __('messages.cover_letter_updated')
        );
    }

    private function resolveOwnedCoverLetter(Request $request, int $id, $user): ?CoverLetter
    {
        if ($user) {
            return $this->repository->findByIdForUser($id, $user->id);
        }

        $anonymousUser = $this->anonymousUserService->resolve($request);

        if (! $anonymousUser) {
            return null;
        }

        return $this->anonymousUserService->findGuestCoverLetter($anonymousUser, $id);
    }

    private function respondWithPdf(CoverLetter $coverLetter, CoverLetterTemplate $template, bool $shouldReturnUrl)
    {
        try {
            if ($shouldReturnUrl) {
                $url = $this->pdfService->generatePdf($coverLetter, $template, true);

                return $this->successResponse(
                    [
                        'url' => $url,
                        'cover_letter_id' => $coverLetter->id,
                    ],
                    __('messages.pdf_generated_successfully')
                );
            }

            return $this->pdfService->generatePdf($coverLetter, $template);
        } catch (\Exception $e) {
            return $this->errorResponse(__('messages.pdf_generation_failed').': '.$e->getMessage(), 500);
        }
    }
}
