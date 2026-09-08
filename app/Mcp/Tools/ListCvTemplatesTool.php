<?php

namespace App\Mcp\Tools;

use App\Mcp\Tools\Concerns\InteractsWithToolPayload;
use App\Models\Template;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
#[Description('List active CV templates. Use a template id with create_cv or render_cv_pdf.')]
class ListCvTemplatesTool extends Tool
{
    use InteractsWithToolPayload;

    public function handle(Request $request): Response
    {
        $templates = Template::query()
            ->where('is_active', true)
            ->get()
            ->map(fn (Template $template) => [
                'id' => $template->id,
                'name' => $template->name,
                'preview' => $template->preview_url,
                'description' => $template->description,
                'supports_image' => (bool) $template->supports_image,
            ]);

        return $this->ok($templates, __('messages.templates_retrieved'));
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
