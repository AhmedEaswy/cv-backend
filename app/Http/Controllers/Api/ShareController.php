<?php

namespace App\Http\Controllers\Api;

use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ShareController extends BaseApiController
{
    /**
     * Get all active templates (public endpoint).
     */
    public function templates(Request $request)
    {
        $templates = Template::where('is_active', true)
            ->get()
            ->map(function ($template) {
                return [
                    'id' => $template->id,
                    'name' => $template->name,
                    'preview' => $template->preview ? Storage::disk('public')->url($template->preview) : null,
                    'description' => $template->description,
                    'created_at' => $template->created_at->toIso8601String(),
                    'updated_at' => $template->updated_at->toIso8601String(),
                ];
            });

        return $this->successResponse($templates, __('messages.templates_retrieved'));
    }
}
