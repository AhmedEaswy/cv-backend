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
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
#[Description('Get the authenticated user public profile.')]
class GetPublicProfileTool extends Tool
{
    use InteractsWithToolPayload;
    use RequiresAbility;

    public function __construct(
        private PublicProfileRepository $repository,
        private PublicProfileDataMapper $dataMapper,
    ) {}

    protected function requiredAbility(): string
    {
        return AgentAbilities::PROFILE_READ;
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

        return $this->ok(
            $this->dataMapper->formatPublicProfileResponse($profile),
            __('messages.public_profile_retrieved'),
        );
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
