<?php

namespace App\Http\Controllers;

use App\Models\CoverLetter;
use App\Models\CoverLetterTemplate;
use App\Services\CoverLetterDataMapper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class CoverLetterPreviewController extends Controller
{
    public function __construct(
        private CoverLetterDataMapper $dataMapper
    ) {
    }

    public function preview(Request $request, string $id)
    {
        $coverLetter = CoverLetter::find($id);

        if (! $coverLetter) {
            abort(404, __('messages.cover_letter_not_found'));
        }

        $templateId = $request->query('template_id') ?? $coverLetter->cover_letter_template_id;

        if ($templateId) {
            $template = CoverLetterTemplate::where('id', $templateId)
                ->where('is_active', true)
                ->first();

            if (! $template) {
                abort(404, __('messages.template_not_found_or_inactive'));
            }
        } else {
            $template = CoverLetterTemplate::where('is_default', true)
                ->where('is_active', true)
                ->first();

            if (! $template) {
                $template = CoverLetterTemplate::where('is_active', true)->first();

                if (! $template) {
                    abort(404, __('messages.no_template_available'));
                }
            }
        }

        $viewName = strtolower(str_replace(' ', '-', $template->name));
        $viewPath = "templates.cover-letter.{$viewName}";

        if (! view()->exists($viewPath)) {
            abort(404, __('messages.template_view_not_found'));
        }

        $coverLetterData = $this->dataMapper->formatCoverLetterResponse($coverLetter);
        App::setLocale($coverLetterData['language'] ?? 'en');

        return view($viewPath, [
            'coverLetter' => $coverLetterData,
            'preview' => true,
        ]);
    }
}
