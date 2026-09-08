<?php

namespace App\Http\Controllers\Api;

use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ShareController extends BaseApiController
{
    /**
     * Get active CV templates (public).
     * Without `page` → flat list (portal / agents).
     * With `page` → `{ data, meta }` for load-more pagination.
     */
    public function templates(Request $request)
    {
        $query = Template::query()
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('name');

        return $this->paginatedOrAll(
            $query,
            $request,
            function ($template) {
                return [
                    'id' => $template->id,
                    'name' => $template->name,
                    'preview' => $template->preview ? Storage::disk('public')->url($template->preview) : null,
                    'description' => $template->description,
                    'supports_image' => (bool) $template->supports_image,
                    'is_default' => (bool) $template->is_default,
                    'created_at' => $template->created_at->toIso8601String(),
                    'updated_at' => $template->updated_at->toIso8601String(),
                ];
            },
            __('messages.templates_retrieved')
        );
    }
}
