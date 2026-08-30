<?php

namespace App\Mcp\Tools;

use App\Mcp\Tools\Concerns\InteractsWithToolPayload;
use App\Mcp\Tools\Concerns\RequiresAbility;
use App\Repositories\PublicProfileRepository;
use App\Support\AgentAbilities;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;

#[IsDestructive]
#[Description('Delete the authenticated user public profile.')]
class DeletePublicProfileTool extends Tool
{
    use InteractsWithToolPayload;
    use RequiresAbility;

    public function __construct(private PublicProfileRepository $repository) {}

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

        $this->repository->delete($profile);

        return $this->ok(null, __('messages.public_profile_deleted'));
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
