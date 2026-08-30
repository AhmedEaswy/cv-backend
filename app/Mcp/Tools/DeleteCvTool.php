<?php

namespace App\Mcp\Tools;

use App\Mcp\Tools\Concerns\InteractsWithToolPayload;
use App\Mcp\Tools\Concerns\RequiresAbility;
use App\Repositories\CVRepositoryInterface;
use App\Services\TrackingService;
use App\Support\AgentAbilities;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;

#[IsDestructive]
#[Description('Permanently delete a saved CV by id.')]
class DeleteCvTool extends Tool
{
    use InteractsWithToolPayload;
    use RequiresAbility;

    public function __construct(
        private CVRepositoryInterface $cvRepository,
        private TrackingService $trackingService,
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
        ]);

        $profile = $this->cvRepository->findByIdForUser((int) $validated['id'], $request->user()->id);

        if (! $profile) {
            return $this->fail(__('messages.cv_not_found'));
        }

        $this->cvRepository->update($profile, $this->trackingService->capture(request()));
        $this->cvRepository->delete($profile);

        return $this->ok(null, __('messages.cv_deleted'));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->required(),
        ];
    }
}
