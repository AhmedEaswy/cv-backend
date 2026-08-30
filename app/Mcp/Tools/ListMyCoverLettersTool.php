<?php

namespace App\Mcp\Tools;

use App\Mcp\Tools\Concerns\InteractsWithToolPayload;
use App\Mcp\Tools\Concerns\RequiresAbility;
use App\Repositories\CoverLetterRepository;
use App\Services\CoverLetterDataMapper;
use App\Support\AgentAbilities;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
#[Description('List cover letters saved on the authenticated account.')]
class ListMyCoverLettersTool extends Tool
{
    use InteractsWithToolPayload;
    use RequiresAbility;

    public function __construct(
        private CoverLetterRepository $repository,
        private CoverLetterDataMapper $dataMapper,
    ) {}

    protected function requiredAbility(): string
    {
        return AgentAbilities::COVER_LETTER_READ;
    }

    public function handle(Request $request): Response
    {
        if ($denied = $this->denyUnlessAble($request)) {
            return $denied;
        }

        $validated = $request->validate([
            'language' => 'sometimes|string|max:10|in:en,ar,tr',
        ]);

        $items = $this->repository
            ->getAllForUser($request->user()->id, $validated['language'] ?? null)
            ->map(fn ($item) => $this->dataMapper->formatCoverLetterResponse($item));

        return $this->ok($items, __('messages.cover_letters_retrieved'));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'language' => $schema->string(),
        ];
    }
}
