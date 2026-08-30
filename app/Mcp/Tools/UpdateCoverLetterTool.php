<?php

namespace App\Mcp\Tools;

use App\Mcp\Tools\Concerns\InteractsWithToolPayload;
use App\Mcp\Tools\Concerns\RequiresAbility;
use App\Repositories\CoverLetterRepository;
use App\Services\CoverLetterDataMapper;
use App\Services\TrackingService;
use App\Support\AgentAbilities;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Update a saved cover letter.')]
class UpdateCoverLetterTool extends Tool
{
    use InteractsWithToolPayload;
    use RequiresAbility;

    public function __construct(
        private CoverLetterRepository $repository,
        private CoverLetterDataMapper $dataMapper,
        private TrackingService $trackingService,
    ) {}

    protected function requiredAbility(): string
    {
        return AgentAbilities::COVER_LETTER_WRITE;
    }

    public function handle(Request $request): Response
    {
        if ($denied = $this->denyUnlessAble($request)) {
            return $denied;
        }

        $validated = $request->validate([
            'id' => 'required|integer',
            'name' => 'sometimes|string|max:255',
            'language' => 'sometimes|string|max:10|in:en,ar,tr',
            'cover_letter_template_id' => 'sometimes|nullable|integer',
            'user_data' => 'sometimes',
        ]);

        $item = $this->repository->findByIdForUser((int) $validated['id'], $request->user()->id);

        if (! $item) {
            return $this->fail(__('messages.cover_letter_not_found'));
        }

        $updateData = $this->trackingService->capture(request());

        foreach (['name', 'language', 'cover_letter_template_id'] as $field) {
            if (array_key_exists($field, $validated)) {
                $updateData[$field] = $validated[$field];
            }
        }

        $userData = $this->decodeUserData($request);

        if ($userData !== []) {
            $mapped = $this->dataMapper->mapUserDataToCoverLetter($userData);
            if (isset($mapped['info'])) {
                $updateData['info'] = $mapped['info'];
            }
            if (isset($mapped['experiences'])) {
                $updateData['experiences'] = $mapped['experiences'];
            }
        }

        $updated = $this->repository->update($item, $updateData);

        return $this->ok($this->dataMapper->formatCoverLetterResponse($updated), __('messages.cover_letter_updated'));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->required(),
            'name' => $schema->string(),
            'language' => $schema->string(),
            'cover_letter_template_id' => $schema->integer(),
            'user_data' => $schema->string(),
        ];
    }
}
