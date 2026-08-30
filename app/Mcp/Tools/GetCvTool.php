<?php

namespace App\Mcp\Tools;

use App\Mcp\Tools\Concerns\InteractsWithToolPayload;
use App\Mcp\Tools\Concerns\RequiresAbility;
use App\Repositories\CVRepositoryInterface;
use App\Services\CVDataMapper;
use App\Support\AgentAbilities;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
#[Description('Get one saved CV by id for the authenticated account.')]
class GetCvTool extends Tool
{
    use InteractsWithToolPayload;
    use RequiresAbility;

    public function __construct(
        private CVRepositoryInterface $cvRepository,
        private CVDataMapper $dataMapper,
    ) {}

    protected function requiredAbility(): string
    {
        return AgentAbilities::CV_READ;
    }

    public function handle(Request $request): Response
    {
        if ($denied = $this->denyUnlessAble($request)) {
            return $denied;
        }

        $validated = $request->validate([
            'id' => 'required|integer',
        ]);

        $profile = $this->cvRepository->findByIdForUser((int) $validated['id'], $request->user()->id);

        if (! $profile) {
            return $this->fail(__('messages.cv_not_found'));
        }

        return $this->ok($this->dataMapper->formatProfileResponse($profile), __('messages.cv_retrieved'));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('CV id.')->required(),
        ];
    }
}
