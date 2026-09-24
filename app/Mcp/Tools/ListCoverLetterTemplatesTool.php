<?php

namespace App\Mcp\Tools;

use App\Mcp\Tools\Concerns\InteractsWithToolPayload;
use App\Repositories\CoverLetterRepository;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
#[Description('List active cover-letter templates.')]
class ListCoverLetterTemplatesTool extends Tool
{
    use InteractsWithToolPayload;

    public function __construct(private CoverLetterRepository $repository) {}

    public function handle(Request $request): Response
    {
        $locale = is_string($request->get('locale')) ? $request->get('locale') : app()->getLocale();

        $templates = $this->repository->getActiveTemplates()->map(fn ($template) => [
            'id' => $template->id,
            'name' => $template->name,
            'preview' => $template->resolvedPreviewUrl($locale),
            'description' => $template->description,
            'is_default' => $template->is_default,
        ]);

        return $this->ok($templates, __('messages.templates_retrieved'));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'locale' => $schema->string()
                ->description('UI language code (en or ar). Arabic returns Arabic preview images when available.')
                ->optional(),
        ];
    }
}
