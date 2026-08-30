<?php

namespace App\Mcp\Tools;

use App\Mcp\Tools\Concerns\InteractsWithToolPayload;
use App\Repositories\CVRepositoryInterface;
use App\Services\CVPDFService;
use App\Services\CvPhotoService;
use App\Services\TrackingService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Render a CV as PDF and return a public URL. Pass profile_id or user_data JSON plus a template_id.')]
class RenderCvPdfTool extends Tool
{
    use InteractsWithToolPayload;

    public function __construct(
        private CVRepositoryInterface $cvRepository,
        private CVPDFService $pdfService,
        private TrackingService $trackingService,
        private CvPhotoService $photoService,
    ) {}

    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'template_id' => 'required|integer',
            'profile_id' => 'sometimes|nullable|integer',
            'name' => 'sometimes|string|max:255',
            'language' => 'sometimes|string|max:10|in:en,ar,tr',
            'user_data' => 'sometimes',
        ]);

        $template = $this->cvRepository->findActiveTemplate((int) $validated['template_id']);

        if (! $template) {
            return $this->fail(__('messages.template_not_found_or_inactive'));
        }

        $http = request();
        $user = $request->user();

        if (! empty($validated['profile_id'])) {
            $profile = $this->cvRepository->findById((int) $validated['profile_id']);

            if (! $profile || ($user && $profile->user_id && $profile->user_id !== $user->id)) {
                return $this->fail(__('messages.cv_not_found'));
            }

            $this->cvRepository->update($profile, $this->trackingService->capture($http));
        } else {
            $userData = $this->decodeUserData($request);

            if ($userData === []) {
                return $this->fail('Provide profile_id or user_data.');
            }

            $profile = $this->pdfService->createTemporaryProfile(
                $this->photoService->processUserDataPhoto($userData),
                $user?->id,
                $validated['name'] ?? 'CV',
                $validated['language'] ?? 'en',
            );

            if (! $profile->exists) {
                $profile->save();
                $profile->fill($this->trackingService->capture($http))->save();
            }
        }

        try {
            $url = $this->pdfService->generatePdf($profile, $template, true);
        } catch (\Throwable $e) {
            return $this->fail(__('messages.pdf_generation_failed').': '.$e->getMessage());
        }

        return $this->ok(['url' => $url], __('messages.pdf_generated_successfully'));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'template_id' => $schema->integer()->description('Active CV template id from list_cv_templates.')->required(),
            'profile_id' => $schema->integer()->description('Existing CV id to print.'),
            'user_data' => $schema->string()->description('JSON CV fields when profile_id is omitted. firstName and lastName required.'),
            'name' => $schema->string()->description('Title used when creating a temporary CV.'),
            'language' => $schema->string()->default('en'),
        ];
    }
}
