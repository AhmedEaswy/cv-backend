<?php

namespace App\Mcp\Tools;

use App\Mcp\Tools\Concerns\InteractsWithToolPayload;
use App\Repositories\CoverLetterRepository;
use App\Services\CoverLetterPDFService;
use App\Services\TrackingService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Render a cover letter as PDF and return a public URL.')]
class RenderCoverLetterPdfTool extends Tool
{
    use InteractsWithToolPayload;

    public function __construct(
        private CoverLetterRepository $repository,
        private CoverLetterPDFService $pdfService,
        private TrackingService $trackingService,
    ) {}

    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'template_id' => 'required|integer',
            'cover_letter_id' => 'sometimes|nullable|integer',
            'name' => 'sometimes|string|max:255',
            'language' => 'sometimes|string|max:10|in:en,ar,tr',
            'user_data' => 'sometimes',
        ]);

        $template = $this->repository->findActiveTemplate((int) $validated['template_id']);

        if (! $template) {
            return $this->fail(__('messages.template_not_found_or_inactive'));
        }

        $user = $request->user();

        if (! empty($validated['cover_letter_id'])) {
            $coverLetter = $this->repository->findById((int) $validated['cover_letter_id']);

            if (! $coverLetter || ($user && $coverLetter->user_id && $coverLetter->user_id !== $user->id)) {
                return $this->fail(__('messages.cover_letter_not_found'));
            }

            $this->repository->update($coverLetter, $this->trackingService->capture(request()));
        } else {
            $userData = $this->decodeUserData($request);

            if ($userData === []) {
                return $this->fail('Provide cover_letter_id or user_data.');
            }

            $coverLetter = $this->pdfService->createTemporaryCoverLetter(
                $userData,
                $user?->id,
                $validated['name'] ?? 'Cover Letter',
                $validated['language'] ?? 'en',
            );

            if (! $coverLetter->exists) {
                $coverLetter = $this->repository->create(array_merge(
                    $coverLetter->getAttributes(),
                    $this->trackingService->capture(request()),
                ));
            }
        }

        try {
            $url = $this->pdfService->generatePdf($coverLetter, $template, true);
        } catch (\Throwable $e) {
            return $this->fail(__('messages.pdf_generation_failed').': '.$e->getMessage());
        }

        return $this->ok(['url' => $url], __('messages.pdf_generated_successfully'));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'template_id' => $schema->integer()->description('Active cover-letter template id.')->required(),
            'cover_letter_id' => $schema->integer()->description('Existing cover letter id to print.'),
            'user_data' => $schema->string()->description('JSON cover-letter fields when cover_letter_id is omitted.'),
            'name' => $schema->string(),
            'language' => $schema->string()->default('en'),
        ];
    }
}
