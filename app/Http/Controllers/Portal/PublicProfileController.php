<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\PublicProfile;
use App\Models\PublicProfileTemplate;
use App\Repositories\PublicProfileRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PublicProfileController extends Controller
{
    public function __construct(private readonly PublicProfileRepository $repository)
    {
    }

    public function edit(Request $request): View
    {
        $profile = $this->repository->findForUser($request->user()->id);

        return view('portal.public-profile.edit', [
            'profile' => $profile,
            'templates' => $this->activeTemplates(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $existing = $this->repository->findForUser($request->user()->id);

        if ($existing) {
            return redirect()->route('portal.public-profile.edit')
                ->withErrors(['profile' => __('messages.public_profile_already_exists')]);
        }

        $data = $this->validateProfile($request);

        $templateId = $data['public_profile_template_id']
            ?? $this->repository->getDefaultTemplate()?->id;

        $profile = $this->repository->create([
            'user_id' => $request->user()->id,
            'public_profile_template_id' => $templateId,
            'slug' => $data['slug'] ?? null,
            'language' => $data['language'] ?? 'en',
            'is_public' => $data['is_public'] ?? false,
            'enable_contact_form' => $data['enable_contact_form'] ?? false,
            'contact_form_recipient' => $data['contact_form_recipient'] ?? null,
            'headline' => $data['headline'] ?? null,
            'about' => $data['about'] ?? null,
            'sections_order' => $data['sections_order'] ?? null,
            'info' => [
                'firstName' => $request->user()->first_name,
                'lastName' => $request->user()->last_name,
                'email' => $request->user()->email,
            ],
        ]);

        return redirect()->route('portal.public-profile.edit')
            ->with('status', __('messages.public_profile_created'));
    }

    public function update(Request $request): RedirectResponse
    {
        $profile = $this->repository->findForUser($request->user()->id);

        if (! $profile) {
            return redirect()->route('portal.public-profile.edit')
                ->withErrors(['profile' => __('messages.public_profile_not_found')]);
        }

        $data = $this->validateProfile($request, $profile);

        $update = [
            'public_profile_template_id' => $data['public_profile_template_id'] ?? $profile->public_profile_template_id,
            'slug' => $data['slug'] ?? $profile->slug,
            'language' => $data['language'] ?? $profile->language,
            'is_public' => $data['is_public'] ?? false,
            'enable_contact_form' => $data['enable_contact_form'] ?? false,
            'contact_form_recipient' => $data['contact_form_recipient'] ?? null,
            'headline' => $data['headline'] ?? null,
            'about' => $data['about'] ?? null,
            'sections_order' => $data['sections_order'] ?? null,
        ];

        $this->repository->update($profile, $update);

        return back()->with('status', __('messages.public_profile_updated'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $profile = $this->repository->findForUser($request->user()->id);

        if (! $profile) {
            return back()->withErrors(['profile' => __('messages.public_profile_not_found')]);
        }

        $this->repository->delete($profile);

        return redirect()->route('portal.dashboard')
            ->with('status', __('messages.public_profile_deleted'));
    }

    public function contactMessages(Request $request): View
    {
        $profile = $this->repository->findForUser($request->user()->id);

        $messages = $profile
            ? $profile->contactMessages()->paginate(20)
            : collect();

        return view('portal.public-profile.inbox', [
            'profile' => $profile,
            'messages' => $messages,
        ]);
    }

    public function markRead(Request $request, int $id): RedirectResponse
    {
        $profile = $this->repository->findForUser($request->user()->id);

        abort_if($profile === null, 404);

        $message = $profile->contactMessages()->where('id', $id)->first();
        abort_if($message === null, 404);

        $message->markAsRead();

        return back()->with('status', __('messages.contact_message_marked_read'));
    }

    private function validateProfile(Request $request, ?PublicProfile $profile = null): array
    {
        $profileId = $profile?->id;
        $data = $request->validate([
            'public_profile_template_id' => ['nullable', 'integer', 'exists:public_profile_templates,id'],
            'slug' => [
                'nullable',
                'string',
                'min:2',
                'max:80',
                'regex:/^[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/',
                Rule::unique('public_profiles', 'slug')
                    ->ignore($profileId)
                    ->whereNull('deleted_at'),
            ],
            'language' => ['nullable', 'string', 'in:en,ar,tr,es,fr,de,ur'],
            'is_public' => ['sometimes', 'boolean'],
            'enable_contact_form' => ['sometimes', 'boolean'],
            'contact_form_recipient' => ['nullable', 'string', 'email:rfc', 'max:191'],
            'headline' => ['nullable', 'string', 'max:180'],
            'about' => ['nullable', 'string', 'max:2000'],
            'sections_order' => ['nullable', 'array'],
        ], [
            'slug.regex' => __('messages.slug_invalid'),
            'slug.unique' => __('messages.slug_taken'),
        ]);

        if (($data['enable_contact_form'] ?? false) && empty($data['contact_form_recipient'])) {
            // No-op; we fall back to the owner's email when sending.
        }

        return $data;
    }

    private function activeTemplates()
    {
        return PublicProfileTemplate::where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get(['id', 'name', 'preview', 'description', 'is_default']);
    }
}
