<?php

namespace App\Mcp\Tools;

use App\Mcp\Tools\Concerns\InteractsWithToolPayload;
use App\Mcp\Tools\Concerns\RequiresAbility;
use App\Repositories\PublicProfileRepository;
use App\Services\PublicProfileDataMapper;
use App\Support\AgentAbilities;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Update the authenticated user public profile.')]
class UpdatePublicProfileTool extends Tool
{
    use InteractsWithToolPayload;
    use RequiresAbility;

    public function __construct(
        private PublicProfileRepository $repository,
        private PublicProfileDataMapper $dataMapper,
    ) {}

    protected function requiredAbility(): string
    {
        return AgentAbilities::PROFILE_WRITE;
    }

    public function handle(Request $request): Response
    {
        if ($denied = $this->denyUnlessAble($request)) {
            return $denied;
        }

        $profile = $this->repository->findForUser($request->user()->id);

        if (! $profile) {
            return $this->fail(__('messages.public_profile_not_found'));
        }

        $validated = $request->validate([
            'slug' => 'sometimes|nullable|string|max:100|alpha_dash|unique:public_profiles,slug,'.$profile->id,
            'language' => 'sometimes|string|max:10|in:en,ar,tr',
            'is_public' => 'sometimes|boolean',
            'headline' => 'sometimes|nullable|string|max:255',
            'about' => 'sometimes|nullable|string',
            'public_profile_template_id' => 'sometimes|nullable|integer',
            'user_data' => 'sometimes',
        ]);

        $updateData = [];

        foreach (['slug', 'language', 'is_public', 'headline', 'about', 'public_profile_template_id'] as $field) {
            if (array_key_exists($field, $validated)) {
                $updateData[$field] = $validated[$field];
            }
        }

        $userData = $this->decodeUserData($request);

        if ($userData !== []) {
            $updateData = array_merge($updateData, $this->dataMapper->mapUserDataToPublicProfile($userData));
        }

        $updated = $this->repository->update($profile, $updateData);

        return $this->ok(
            $this->dataMapper->formatPublicProfileResponse($updated),
            __('messages.public_profile_updated'),
        );
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'slug' => $schema->string(),
            'headline' => $schema->string(),
            'about' => $schema->string(),
            'is_public' => $schema->boolean(),
            'language' => $schema->string(),
            'public_profile_template_id' => $schema->integer(),
            'user_data' => $schema->string(),
        ];
    }
}
