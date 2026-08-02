<?php

namespace App\Http\Controllers;

use App\Models\PublicProfileTemplate;
use App\Repositories\PublicProfileRepository;
use App\Services\PublicProfileDataMapper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class PublicProfilePreviewController extends Controller
{
    public function __construct(
        private PublicProfileRepository $repository,
        private PublicProfileDataMapper $dataMapper,
    ) {
    }

    public function preview(Request $request, string $slug)
    {
        $profile = $this->repository->findPublicBySlug($slug);

        if (! $profile) {
            abort(404, __('messages.public_profile_not_found'));
        }

        $template = $this->resolveTemplate(
            $request->query('template_id') ?? $profile->public_profile_template_id
        );

        $viewName = strtolower(str_replace(' ', '-', $template->name));
        $viewPath = "templates.public-profile.{$viewName}";

        if (! view()->exists($viewPath)) {
            abort(404, __('messages.template_view_not_found'));
        }

        $profileData = $this->dataMapper->formatPublicProfileResponse($profile);
        App::setLocale($profileData['language'] ?? 'en');

        return view($viewPath, [
            'profile' => $profileData,
            'preview' => true,
        ]);
    }

    private function resolveTemplate(mixed $templateId): PublicProfileTemplate
    {
        if ($templateId) {
            $template = $this->repository->findActiveTemplate((int) $templateId);

            if (! $template) {
                abort(404, __('messages.template_not_found_or_inactive'));
            }

            return $template;
        }

        $template = $this->repository->getDefaultTemplate();

        if (! $template) {
            abort(404, __('messages.no_template_available'));
        }

        return $template;
    }
}
