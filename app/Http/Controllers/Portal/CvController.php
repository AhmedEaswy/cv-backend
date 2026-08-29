<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\Template;
use App\Repositories\CVRepositoryInterface;
use App\Services\CVDataMapper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CvController extends Controller
{
    public function __construct(
        private readonly CVRepositoryInterface $repository,
        private readonly CVDataMapper $dataMapper,
    ) {
    }

    public function index(Request $request): View
    {
        $userId = $request->user()->id;

        $cvs = $this->repository
            ->getAllForUser($userId)
            ->sortByDesc('updated_at')
            ->map(fn (Profile $profile) => $this->dataMapper->formatProfileResponse($profile))
            ->values();

        return view('portal.cvs.index', [
            'cvs' => $cvs,
        ]);
    }

    public function create(Request $request): View
    {
        $templates = $this->activeTemplates();

        return view('portal.cvs.create', [
            'templates' => $templates,
            'defaultName' => $this->suggestName($request->user()->full_name),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateCreate($request);

        $profile = $this->repository->create([
            'user_id' => $request->user()->id,
            'name' => $data['name'],
            'language' => $data['language'] ?? 'en',
            'template_id' => $data['template_id'] ?? null,
            'is_public' => false,
            'sections_order' => $data['sections_order'] ?? null,
            'info' => [
                'firstName' => $request->user()->first_name,
                'lastName' => $request->user()->last_name,
                'email' => $request->user()->email,
            ],
        ]);

        return redirect()->route('portal.cvs.edit', $profile->id)
            ->with('status', __('messages.cv_created'));
    }

    public function edit(Request $request, int $id): View
    {
        $profile = $this->findOwnedOrFail($request, $id);
        $cv = $this->dataMapper->formatProfileResponse($profile);

        return view('portal.cvs.edit', [
            'cv' => $cv,
            'profile' => $profile,
            'templates' => $this->activeTemplates(),
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $profile = $this->findOwnedOrFail($request, $id);
        $data = $this->validateUpdate($request);

        $this->repository->update($profile, $data);

        return back()->with('status', __('messages.cv_updated'));
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $profile = $this->findOwnedOrFail($request, $id);
        $this->repository->delete($profile);

        return redirect()->route('portal.cvs.index')
            ->with('status', __('messages.cv_deleted'));
    }

    public function duplicate(Request $request, int $id): RedirectResponse
    {
        $original = $this->findOwnedOrFail($request, $id);
        $userId = $request->user()->id;

        $copy = $this->repository->create([
            'user_id' => $userId,
            'name' => $this->nextCopyName((string) $original->name),
            'language' => $original->language,
            'template_id' => $original->template_id,
            'is_public' => false,
            'sections_order' => $original->sections_order,
            'info' => $original->info,
            'experiences' => $original->experiences,
            'educations' => $original->educations,
            'projects' => $original->projects,
            'interests' => $original->interests,
            'languages' => $original->languages,
        ]);

        return redirect()->route('portal.cvs.edit', $copy->id)
            ->with('status', __('messages.cv_duplicated'));
    }

    public function pdf(Request $request, int $id)
    {
        $profile = $this->findOwnedOrFail($request, $id);
        $template = $profile->template_id
            ? $this->repository->findActiveTemplate((int) $profile->template_id)
            : null;

        if (! $template) {
            return back()->withErrors(['cv' => __('messages.template_not_found_or_inactive')]);
        }

        $view = "templates.cv.{$this->templateViewName($template->name)}";

        if (! view()->exists($view)) {
            return back()->withErrors(['cv' => __('messages.template_view_not_found')]);
        }

        $cv = $this->dataMapper->formatProfileResponse($profile);

        return response()->view($view, ['cv' => $cv, 'preview' => true], 200)
            ->header('Content-Type', 'text/html; charset=UTF-8');
    }

    private function findOwnedOrFail(Request $request, int $id): Profile
    {
        $profile = $this->repository->findByIdForUser($id, $request->user()->id);

        abort_if($profile === null, 404, __('messages.cv_not_found'));

        return $profile;
    }

    private function activeTemplates()
    {
        return Template::where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get(['id', 'name', 'preview', 'description', 'is_default', 'supports_image']);
    }

    private function suggestName(string $userName): string
    {
        $base = trim($userName) !== '' ? trim($userName).' — CV' : __('messages.default_cv_name');

        return $base.' ('.date('Y').')';
    }

    private function nextCopyName(string $original): string
    {
        if (Str::contains($original, ' (Copy')) {
            return preg_replace('/\(Copy( \d+)?\)$/', '(Copy '.((int) (Str::afterLast($original, ' ')) + 1 ?: 2).')', $original)
                ?: $original.' (Copy)';
        }

        return $original.' (Copy)';
    }

    private function templateViewName(string $templateName): string
    {
        return strtolower(str_replace(' ', '-', $templateName));
    }

    private function validateCreate(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'language' => ['nullable', 'string', 'in:en,ar,tr,es,fr,de,ur'],
            'template_id' => ['nullable', 'integer', 'exists:templates,id'],
            'sections_order' => ['nullable', 'array'],
        ]);
    }

    private function validateUpdate(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'language' => ['nullable', 'string', 'in:en,ar,tr,es,fr,de,ur'],
            'is_public' => ['sometimes', 'boolean'],
        ]);
    }
}
