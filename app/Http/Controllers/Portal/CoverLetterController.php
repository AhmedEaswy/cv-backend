<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\CoverLetter;
use App\Models\CoverLetterTemplate;
use App\Repositories\CoverLetterRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CoverLetterController extends Controller
{
    public function __construct(private readonly CoverLetterRepository $repository)
    {
    }

    public function index(Request $request): View
    {
        $letters = $this->repository
            ->getAllForUser($request->user()->id)
            ->sortByDesc('updated_at')
            ->values();

        return view('portal.cover-letters.index', [
            'coverLetters' => $letters,
        ]);
    }

    public function create(Request $request): View
    {
        return view('portal.cover-letters.create', [
            'templates' => $this->activeTemplates(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateCreate($request);

        $letter = $this->repository->create([
            'user_id' => $request->user()->id,
            'name' => $data['name'],
            'cover_letter_template_id' => $data['cover_letter_template_id'] ?? null,
            'language' => $data['language'] ?? 'en',
            'is_public' => false,
            'info' => [
                'firstName' => $request->user()->first_name,
                'lastName' => $request->user()->last_name,
                'email' => $request->user()->email,
                'recipientName' => '',
                'companyName' => '',
                'position' => '',
            ],
        ]);

        return redirect()->route('portal.cover-letters.edit', $letter->id)
            ->with('status', __('messages.cover_letter_created'));
    }

    public function edit(Request $request, int $id): View
    {
        $letter = $this->findOwnedOrFail($request, $id);

        return view('portal.cover-letters.edit', [
            'letter' => $letter,
            'templates' => $this->activeTemplates(),
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $letter = $this->findOwnedOrFail($request, $id);
        $data = $this->validateUpdate($request);

        $this->repository->update($letter, $data);

        return back()->with('status', __('messages.cover_letter_updated'));
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $letter = $this->findOwnedOrFail($request, $id);
        $this->repository->delete($letter);

        return redirect()->route('portal.cover-letters.index')
            ->with('status', __('messages.cover_letter_deleted'));
    }

    public function duplicate(Request $request, int $id): RedirectResponse
    {
        $original = $this->findOwnedOrFail($request, $id);

        $copy = $this->repository->create([
            'user_id' => $request->user()->id,
            'name' => $this->nextCopyName((string) $original->name),
            'cover_letter_template_id' => $original->cover_letter_template_id,
            'language' => $original->language,
            'is_public' => false,
            'sections_order' => $original->sections_order,
            'info' => $original->info,
            'experiences' => $original->experiences,
        ]);

        return redirect()->route('portal.cover-letters.edit', $copy->id)
            ->with('status', __('messages.cover_letter_duplicated'));
    }

    private function findOwnedOrFail(Request $request, int $id): CoverLetter
    {
        $letter = $this->repository->findByIdForUser($id, $request->user()->id);

        abort_if($letter === null, 404, __('messages.cover_letter_not_found'));

        return $letter;
    }

    private function activeTemplates()
    {
        return CoverLetterTemplate::where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get(['id', 'name', 'preview', 'description', 'is_default']);
    }

    private function nextCopyName(string $original): string
    {
        if (Str::contains($original, ' (Copy')) {
            return $original.' 2';
        }

        return $original.' (Copy)';
    }

    private function validateCreate(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'language' => ['nullable', 'string', 'in:en,ar,tr,es,fr,de,ur'],
            'cover_letter_template_id' => ['nullable', 'integer', 'exists:cover_letter_templates,id'],
        ]);
    }

    private function validateUpdate(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'language' => ['nullable', 'string', 'in:en,ar,tr,es,fr,de,ur'],
            'is_public' => ['sometimes', 'boolean'],
            'info' => ['nullable', 'array'],
            'experiences' => ['nullable', 'array'],
        ]);
    }
}
