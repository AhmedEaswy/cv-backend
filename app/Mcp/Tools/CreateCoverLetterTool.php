<?php

namespace App\Mcp\Tools;

use App\Mcp\Tools\Concerns\InteractsWithToolPayload;
use App\Repositories\CoverLetterRepository;
use App\Services\CoverLetterDataMapper;
use App\Services\TrackingService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Create a cover letter from user_data JSON. Works without auth.')]
class CreateCoverLetterTool extends Tool
{
    use InteractsWithToolPayload;

    public function __construct(
        private CoverLetterRepository $repository,
        private CoverLetterDataMapper $dataMapper,
        private TrackingService $trackingService,
    ) {}

    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'language' => 'sometimes|string|max:10|in:en,ar,tr',
            'cover_letter_template_id' => 'sometimes|nullable|integer',
            'user_data' => 'sometimes',
        ]);

        $mappedData = $this->dataMapper->mapUserDataToCoverLetter($this->decodeUserData($request));

        $coverLetter = $this->repository->create(array_merge([
            'user_id' => $request->user()?->id,
            'name' => $validated['name'],
            'language' => $validated['language'] ?? 'en',
            'cover_letter_template_id' => $validated['cover_letter_template_id'] ?? null,
            'info' => $mappedData['info'] ?? null,
            'experiences' => $mappedData['experiences'] ?? null,
        ], $this->trackingService->capture(request())));

        return $this->ok(
            $this->dataMapper->formatCoverLetterResponse($coverLetter),
            __('messages.cover_letter_created'),
        );
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('Cover letter title.')->required(),
            'user_data' => $schema->string()->description('JSON: firstName, lastName, email, jobTitle, companyName, recipientName, subject, body, closing, experiences[].'),
            'cover_letter_template_id' => $schema->integer()->description('Optional template id.'),
            'language' => $schema->string()->default('en'),
        ];
    }
}
