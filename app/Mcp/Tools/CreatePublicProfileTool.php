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

#[Description('Create the authenticated user public profile. One profile per account.')]
class CreatePublicProfileTool extends Tool
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

        if ($this->repository->findForUser($request->user()->id)) {
            return $this->fail(__('messages.public_profile_already_exists'));
        }

        $validated = $request->validate([
            'slug' => 'sometimes|nullable|string|max:100|alpha_dash|unique:public_profiles,slug',
            'language' => 'sometimes|string|max:10|in:en,ar,tr',
            'is_public' => 'sometimes|boolean',
            'headline' => 'sometimes|nullable|string|max:255',
            'about' => 'sometimes|nullable|string',
            'public_profile_template_id' => 'sometimes|nullable|integer',
            'user_data' => 'sometimes',
        ]);

        $mappedData = $this->dataMapper->mapUserDataToPublicProfile($this->decodeUserData($request));
        $templateId = $validated['public_profile_template_id']
            ?? $this->repository->getDefaultTemplate()?->id;

        $profile = $this->repository->create(array_merge([
            'user_id' => $request->user()->id,
            'slug' => $validated['slug'] ?? null,
            'language' => $validated['language'] ?? 'en',
            'is_public' => $validated['is_public'] ?? true,
            'headline' => $validated['headline'] ?? null,
            'about' => $validated['about'] ?? null,
            'public_profile_template_id' => $templateId,
        ], $mappedData));

        return $this->ok(
            $this->dataMapper->formatPublicProfileResponse($profile),
            __('messages.public_profile_created'),
        );
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'slug' => $schema->string()->description('Public URL slug, e.g. jane-doe.'),
            'headline' => $schema->string(),
            'about' => $schema->string(),
            'is_public' => $schema->boolean()->default(true),
            'language' => $schema->string()->default('en'),
            'public_profile_template_id' => $schema->integer(),
            'user_data' => $schema->string()->description('JSON profile fields (firstName, lastName, jobTitle, socialLinks, …).'),
        ];
    }
}
