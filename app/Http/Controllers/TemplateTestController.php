<?php

namespace App\Http\Controllers;

use App\Models\CoverLetter;
use App\Models\Profile;
use App\Services\CoverLetterDataMapper;
use App\Services\CVDataMapper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class TemplateTestController extends Controller
{
    public function __construct(
        private CVDataMapper $cvMapper,
        private CoverLetterDataMapper $coverLetterMapper,
    ) {
    }

    public function cv(Request $request, string $template)
    {
        $this->ensureLocal();

        $viewName = strtolower(str_replace(' ', '-', $template));
        $viewPath = "templates.cv.{$viewName}";

        if (! view()->exists($viewPath)) {
            abort(404, "CV template view [{$viewName}] not found.");
        }

        $profile = $request->filled('profile_id')
            ? Profile::findOrFail($request->query('profile_id'))
            : Profile::query()->latest('id')->first();

        if (! $profile) {
            abort(404, 'No profiles found. Run LocalFakeDataSeeder first.');
        }

        $cvData = $this->cvMapper->formatProfileResponse($profile);
        App::setLocale($request->query('lang', $cvData['language'] ?? 'en'));

        return view($viewPath, [
            'cv' => $cvData,
            'preview' => true,
        ]);
    }

    public function coverLetter(Request $request, string $template)
    {
        $this->ensureLocal();

        $viewName = strtolower(str_replace(' ', '-', $template));
        $viewPath = "templates.cover-letter.{$viewName}";

        if (! view()->exists($viewPath)) {
            abort(404, "Cover letter template view [{$viewName}] not found.");
        }

        $coverLetter = $request->filled('cover_letter_id')
            ? CoverLetter::findOrFail($request->query('cover_letter_id'))
            : CoverLetter::query()->latest('id')->first();

        if (! $coverLetter) {
            abort(404, 'No cover letters found. Run LocalFakeDataSeeder first.');
        }

        $data = $this->coverLetterMapper->formatCoverLetterResponse($coverLetter);
        App::setLocale($request->query('lang', $data['language'] ?? 'en'));

        return view($viewPath, [
            'coverLetter' => $data,
            'preview' => true,
        ]);
    }

    private function ensureLocal(): void
    {
        if (! app()->environment('local')) {
            abort(404);
        }
    }
}
