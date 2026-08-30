<?php

namespace App\Mcp\Tools;

use App\Mcp\Tools\Concerns\InteractsWithToolPayload;
use App\Repositories\CVRepositoryInterface;
use App\Services\CVDataMapper;
use App\Services\CVPDFService;
use App\Services\CvPhotoService;
use App\Services\TrackingService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Create a CV from structured user_data. If template_id is set, also generate a PDF and return its URL. Works without auth.')]
class CreateCvTool extends Tool
{
    use InteractsWithToolPayload;

    public function __construct(
        private CVRepositoryInterface $cvRepository,
        private CVDataMapper $dataMapper,
        private CVPDFService $pdfService,
        private TrackingService $trackingService,
        private CvPhotoService $photoService,
    ) {}

    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'language' => 'sometimes|string|max:10|in:en,ar,tr',
            'template_id' => 'sometimes|nullable|integer',
            'user_data' => 'sometimes',
        ]);

        $userData = $this->photoService->processUserDataPhoto($this->decodeUserData($request));
        $http = request();
        $user = $request->user();
        $tracking = $this->trackingService->capture($http);

        if (! empty($validated['template_id']) && ! $user) {
            $template = $this->cvRepository->findActiveTemplate((int) $validated['template_id']);

            if (! $template) {
                return $this->fail(__('messages.template_not_found_or_inactive'));
            }

            $profile = $this->pdfService->createTemporaryProfile(
                $userData,
                null,
                $validated['name'],
                $validated['language'] ?? 'en',
            );
            $profile->save();
            $profile->fill($tracking)->save();

            try {
                $url = $this->pdfService->generatePdf($profile, $template, true);
            } catch (\Throwable $e) {
                return $this->fail(__('messages.pdf_generation_failed').': '.$e->getMessage());
            }

            return $this->ok(
                ['url' => $url, 'profile' => $this->dataMapper->formatProfileResponse($profile)],
                __('messages.pdf_generated_successfully'),
            );
        }

        $mappedData = $this->dataMapper->mapUserDataToProfile($userData);

        $profile = $this->cvRepository->create(array_merge([
            'user_id' => $user?->id,
            'name' => $validated['name'],
            'language' => $validated['language'] ?? 'en',
            'info' => $mappedData['info'] ?? null,
            'interests' => $mappedData['interests'] ?? null,
            'languages' => $mappedData['languages'] ?? null,
            'experiences' => $mappedData['experiences'] ?? null,
            'projects' => $mappedData['projects'] ?? null,
            'educations' => $mappedData['educations'] ?? null,
        ], $tracking));

        return $this->ok($this->dataMapper->formatProfileResponse($profile), __('messages.cv_created'));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('CV title, e.g. "Software Engineer CV".')->required(),
            'user_data' => $schema->string()->description('JSON object: firstName, lastName, jobTitle, email, phone, summary, skills[], experiences[], educations[], projects[], languages[], interests[]. Dates as YYYY-MM.'),
            'template_id' => $schema->integer()->description('Optional template id. When set without auth, a PDF URL is returned.'),
            'language' => $schema->string()->description('en, ar, or tr.')->default('en'),
        ];
    }
}
