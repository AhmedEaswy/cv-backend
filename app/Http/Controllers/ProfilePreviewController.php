<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\Template;
use App\Services\CVDataMapper;
use Illuminate\Http\Request;

class ProfilePreviewController extends Controller
{
    public function __construct(
        private CVDataMapper $dataMapper
    ) {
    }

    /**
     * Preview profile HTML template with optional template.
     * If template_id is not provided, uses the default template.
     *
     * @param  Request  $request
     * @param  string  $id
     * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory
     */
    public function preview(Request $request, string $id)
    {
        // Find the profile
        $profile = Profile::find($id);

        if (!$profile) {
            abort(404, 'Profile not found');
        }

        // Get template_id priority: query parameter > profile template_id > default template
        $templateId = $request->query('template_id') ?? $profile->template_id;

        if ($templateId) {
            // Find the specified active template
            $template = Template::where('id', $templateId)
                ->where('is_active', true)
                ->first();

            if (!$template) {
                abort(404, 'Template not found or inactive');
            }
        } else {
            // Find the default active template
            $template = Template::where('is_default', true)
                ->where('is_active', true)
                ->first();

            if (!$template) {
                // Fallback: get any active template if no default is set
                $template = Template::where('is_active', true)->first();

                if (!$template) {
                    abort(404, 'No template available');
                }
            }
        }

        // Convert template name to view name (kebab-case)
        $viewName = strtolower(str_replace(' ', '-', $template->name));
        $viewPath = "templates.cv.{$viewName}";

        // Check if view exists
        if (!view()->exists($viewPath)) {
            abort(404, "Template view '{$viewPath}' not found");
        }

        // Format profile data for view (same format as PDF generation)
        $cvData = $this->dataMapper->formatProfileResponse($profile);

        // Return the view
        return view($viewPath, ['cv' => $cvData]);
    }
}
