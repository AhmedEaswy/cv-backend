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
#[Description('List CVs saved on the authenticated account.')]
class ListMyCvsTool extends Tool
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
            'language' => 'sometimes|string|max:10|in:en,ar,tr',
        ]);

        $cvs = $this->cvRepository
            ->getAllForUser($request->user()->id, $validated['language'] ?? null)
            ->map(fn ($profile) => $this->dataMapper->formatProfileResponse($profile));

        return $this->ok($cvs, __('messages.cvs_retrieved'));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'language' => $schema->string()->description('Optional language filter.'),
        ];
    }
}
