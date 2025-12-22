<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\PrintCVRequest;
use App\Http\Requests\Api\StoreCVRequest;
use App\Http\Requests\Api\UpdateCVRequest;
use App\Repositories\CVRepositoryInterface;
use App\Services\CVDataMapper;
use App\Services\CVPDFService;
use Illuminate\Http\Request;

class CVController extends BaseApiController
{
    public function __construct(
        private CVRepositoryInterface $cvRepository,
        private CVDataMapper $dataMapper,
        private CVPDFService $pdfService
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

        $cvs = $profiles->map(fn ($profile) => $this->dataMapper->formatProfileResponse($profile));

        return $this->successResponse($cvs, __('messages.cvs_retrieved'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCVRequest $request)
    {
        $validated = $request->validated();

        // If unauthenticated user provides template_id, generate PDF instead of creating profile
        if (!$request->user() && $request->has('template_id')) {
            return $this->generatePdfFromRequest($request);
        }

        // Get user_id from authenticated user or request
        $userId = $request->user()?->id ?? $request->input('user_id');

        // Map user_data to Profile structure
        $userData = $request->input('user_data', []);
        $mappedData = $this->dataMapper->mapUserDataToProfile($userData);

        $profile = $this->cvRepository->create([
            'user_id' => $userId,
            'name' => $validated['name'],
            'language' => $validated['language'] ?? 'en',
            'sections_order' => $validated['sections_order'] ?? null,
            'info' => $mappedData['info'] ?? null,
            'interests' => $mappedData['interests'] ?? null,
            'languages' => $mappedData['languages'] ?? null,
            'experiences' => $mappedData['experiences'] ?? null,
            'projects' => $mappedData['projects'] ?? null,
            'educations' => $mappedData['educations'] ?? null,
        ]);

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

        if (!$profile) {
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
        $user = $request->user();

        $profile = $this->cvRepository->findByIdForUser($id, $user->id);

        if (!$profile) {
            return $this->errorResponse(__('messages.cv_not_found'), 404);
        }

        $validated = $request->validated();

        // Build update data
        $updateData = [];

        if (isset($validated['name'])) {
            $updateData['name'] = $validated['name'];
        }

        if (isset($validated['language'])) {
            $updateData['language'] = $validated['language'];
        }

        if (isset($validated['sections_order'])) {
            $updateData['sections_order'] = $validated['sections_order'];
        }

        // Handle user_data updates
        if (isset($validated['user_data'])) {
            $userData = $validated['user_data'];
            $mappedData = $this->dataMapper->mapUserDataToProfile($userData);

            if (isset($mappedData['info'])) {
                $updateData['info'] = $mappedData['info'];
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

        if (!$profile) {
            return $this->errorResponse(__('messages.cv_not_found'), 404);
        }

        $this->cvRepository->delete($profile);

        return $this->successResponse(null, __('messages.cv_deleted'));
    }

    /**
     * Generate PDF from CV data.
     */
    public function print(PrintCVRequest $request)
    {
        // $shouldReturnUrl = $request->boolean('return_url');
        $shouldReturnUrl = true;
        $templateId = $request->input('template_id');
        $profileId = $request->input('profile_id');

        // Load template
        $template = $this->cvRepository->findActiveTemplate($templateId);

        if (!$template) {
            return $this->errorResponse(__('messages.template_not_found_or_inactive'), 404);
        }

        // Load existing profile or create temporary one
        if ($profileId) {
            $user = $request->user();
            $profile = $this->cvRepository->findById($profileId);

            // If user is authenticated, verify ownership
            if ($user && $profile && $profile->user_id !== $user->id) {
                return $this->errorResponse(__('messages.cv_not_found'), 404);
            }

            if (!$profile) {
                return $this->errorResponse(__('messages.cv_not_found'), 404);
            }
        } else {
            // Create temporary profile from user_data
            $profile = $this->pdfService->createTemporaryProfile(
                $request->input('user_data', []),
                $request->user()?->id,
                $request->input('name', 'CV'),
                $request->input('language', 'en'),
                $request->input('sections_order')
            );
        }

        // Generate PDF using the service
        try {
            if ($shouldReturnUrl) {
                $url = $this->pdfService->generatePdf($profile, $template, true);

                return $this->successResponse(
                    ['url' => $url],
                    __('messages.pdf_generated_successfully')
                );
            }

            return $this->pdfService->generatePdf($profile, $template);
        } catch (\RuntimeException $e) {
            return $this->errorResponse(__('messages.pdf_generation_failed') . ': ' . $e->getMessage(), 500);
        } catch (\Exception $e) {
            $errorMessage = __('messages.pdf_generation_failed');
            if (app()->environment('production')) {
                $errorMessage .= '. Please contact support if this issue persists.';
            } else {
                $errorMessage .= ': ' . $e->getMessage();
            }

            return $this->errorResponse($errorMessage, 500);
        }
    }

    /**
     * Generate PDF from request data (used in store method for unauthenticated users).
     */
    private function generatePdfFromRequest(Request $request)
    {
        // $shouldReturnUrl = $request->boolean('return_url');
        $shouldReturnUrl = true;
        $templateId = $request->input('template_id');

        // Load template
        $template = $this->cvRepository->findActiveTemplate($templateId);

        if (!$template) {
            return $this->errorResponse(__('messages.template_not_found_or_inactive'), 404);
        }

        // Create temporary profile
        $profile = $this->pdfService->createTemporaryProfile(
            $request->input('user_data', []),
            null,
            $request->input('name', 'CV'),
            $request->input('language', 'en'),
            $request->input('sections_order')
        );

        // Generate PDF using the service
        try {
            if ($shouldReturnUrl) {
                $url = $this->pdfService->generatePdf($profile, $template, true);

                return $this->successResponse(
                    ['url' => $url],
                    __('messages.pdf_generated_successfully')
                );
            }

            return $this->pdfService->generatePdf($profile, $template);
        } catch (\RuntimeException $e) {
            return $this->errorResponse(__('messages.pdf_generation_failed') . ': ' . $e->getMessage(), 500);
        } catch (\Exception $e) {
            $errorMessage = __('messages.pdf_generation_failed');
            if (app()->environment('production')) {
                $errorMessage .= '. Please contact support if this issue persists.';
            } else {
                $errorMessage .= ': ' . $e->getMessage();
            }

            return $this->errorResponse($errorMessage, 500);
        }
    }
}
