<?php

namespace App\Mcp\Tools;

use App\Mcp\Tools\Concerns\InteractsWithToolPayload;
use App\Mcp\Tools\Concerns\RequiresAbility;
use App\Repositories\CVRepositoryInterface;
use App\Services\CVDataMapper;
use App\Services\CvPhotoService;
use App\Services\TrackingService;
use App\Support\AgentAbilities;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Update a saved CV. Pass id and any of name, language, user_data JSON.')]
class UpdateCvTool extends Tool
{
    use InteractsWithToolPayload;
    use RequiresAbility;

    public function __construct(
        private CVRepositoryInterface $cvRepository,
        private CVDataMapper $dataMapper,
        private TrackingService $trackingService,
        private CvPhotoService $photoService,
    ) {}

    protected function requiredAbility(): string
    {
        return AgentAbilities::CV_WRITE;
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
            'user_data' => 'sometimes',
        ]);

        $profile = $this->cvRepository->findByIdForUser((int) $validated['id'], $request->user()->id);

        if (! $profile) {
            return $this->fail(__('messages.cv_not_found'));
        }

        $updateData = $this->trackingService->capture(request());

        if (isset($validated['name'])) {
            $updateData['name'] = $validated['name'];
        }
        if (isset($validated['language'])) {
            $updateData['language'] = $validated['language'];
        }

        $userData = $this->decodeUserData($request);

        if ($userData !== []) {
            $mappedData = $this->dataMapper->mapUserDataToProfile(
                $this->photoService->processUserDataPhoto($userData),
            );

            foreach (['info', 'interests', 'languages', 'experiences', 'projects', 'educations'] as $field) {
                if (isset($mappedData[$field])) {
                    $updateData[$field] = $field === 'info'
                        ? array_merge($profile->info ?? [], $mappedData['info'])
                        : $mappedData[$field];
                }
            }
        }

        $updated = $this->cvRepository->update($profile, $updateData);

        return $this->ok($this->dataMapper->formatProfileResponse($updated), __('messages.cv_updated'));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->required(),
            'name' => $schema->string(),
            'language' => $schema->string(),
            'user_data' => $schema->string()->description('JSON CV fields to merge.'),
        ];
    }
}
