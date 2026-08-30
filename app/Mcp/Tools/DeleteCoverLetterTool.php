<?php

namespace App\Mcp\Tools;

use App\Mcp\Tools\Concerns\InteractsWithToolPayload;
use App\Mcp\Tools\Concerns\RequiresAbility;
use App\Repositories\CoverLetterRepository;
use App\Services\TrackingService;
use App\Support\AgentAbilities;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;

#[IsDestructive]
#[Description('Delete a saved cover letter by id.')]
class DeleteCoverLetterTool extends Tool
{
    use InteractsWithToolPayload;
    use RequiresAbility;

    public function __construct(
        private CoverLetterRepository $repository,
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

        $validated = $request->validate(['id' => 'required|integer']);
        $item = $this->repository->findByIdForUser((int) $validated['id'], $request->user()->id);

        if (! $item) {
            return $this->fail(__('messages.cover_letter_not_found'));
        }

        $this->repository->update($item, $this->trackingService->capture(request()));
        $this->repository->delete($item);

        return $this->ok(null, __('messages.cover_letter_deleted'));
    }

    public function schema(JsonSchema $schema): array
    {
        return ['id' => $schema->integer()->required()];
    }
}
