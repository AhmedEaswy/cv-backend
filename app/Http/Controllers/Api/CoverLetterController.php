<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\PrintCoverLetterRequest;
use App\Http\Requests\Api\StoreCoverLetterRequest;
use App\Http\Requests\Api\UpdateCoverLetterRequest;
use App\Repositories\CoverLetterRepository;
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
        private TrackingService $trackingService
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

        $userId = $request->user()?->id ?? $request->input('user_id');

        $userData = $request->input('user_data', []);
        $mappedData = $this->dataMapper->mapUserDataToCoverLetter($userData);

        $tracking = $this->trackingService->capture($request);

        $coverLetter = $this->repository->create(array_merge([
            'user_id' => $userId,
            'name' => $validated['name'],
            'language' => $validated['language'] ?? 'en',
            'sections_order' => $validated['sections_order'] ?? null,
            'cover_letter_template_id' => $validated['cover_letter_template_id'] ?? null,
            'info' => $mappedData['info'] ?? null,
            'experiences' => $mappedData['experiences'] ?? null,
        ], $tracking));

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
        $user = $request->user();

        $coverLetter = $this->repository->findByIdForUser($id, $user->id);

        if (! $coverLetter) {
            return $this->errorResponse(__('messages.cover_letter_not_found'), 404);
        }

        $validated = $request->validated();

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

        if ($coverLetterId) {
            $user = $request->user();
            $coverLetter = $this->repository->findById($coverLetterId);

            if ($user && $coverLetter && $coverLetter->user_id !== $user->id) {
                return $this->errorResponse(__('messages.cover_letter_not_found'), 404);
            }

            if (! $coverLetter) {
                return $this->errorResponse(__('messages.cover_letter_not_found'), 404);
            }

            $this->repository->update($coverLetter, $this->trackingService->capture($request));
        } else {
            $coverLetter = $this->pdfService->createTemporaryCoverLetter(
                $request->input('user_data', []),
                $request->user()?->id,
                $request->input('name', 'Cover Letter'),
                $request->input('language', 'en'),
                $request->input('sections_order')
            );

            if (! $coverLetter->exists) {
                $tracking = $this->trackingService->capture($request);
                $coverLetter = $this->repository->create(array_merge($coverLetter->getAttributes(), $tracking));
            }
        }

        try {
            if ($shouldReturnUrl) {
                $url = $this->pdfService->generatePdf($coverLetter, $template, true);

                return $this->successResponse(
                    ['url' => $url],
                    __('messages.pdf_generated_successfully')
                );
            }

            return $this->pdfService->generatePdf($coverLetter, $template);
        } catch (\Exception $e) {
            return $this->errorResponse(__('messages.pdf_generation_failed') . ': ' . $e->getMessage(), 500);
        }
    }

    public function templates()
    {
        $templates = $this->repository->getActiveTemplates()->map(fn ($t) => [
            'id' => $t->id,
            'name' => $t->name,
            'preview' => $t->preview_url,
            'description' => $t->description,
            'is_default' => $t->is_default,
        ]);

        return $this->successResponse($templates, __('messages.templates_retrieved'));
    }
}
