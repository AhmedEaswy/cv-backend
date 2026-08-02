<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\StorePublicProfileRequest;
use App\Http\Requests\Api\UpdatePublicProfileRequest;
use App\Repositories\PublicProfileRepository;
use App\Services\PublicProfileDataMapper;
use Illuminate\Http\Request;

class PublicProfileController extends BaseApiController
{
    public function __construct(
        private PublicProfileRepository $repository,
        private PublicProfileDataMapper $dataMapper,
    ) {
    }

    public function show(Request $request)
    {
        $profile = $this->repository->findForUser($request->user()->id);

        if (! $profile) {
            return $this->errorResponse(__('messages.public_profile_not_found'), 404);
        }

        return $this->successResponse(
            $this->dataMapper->formatPublicProfileResponse($profile),
            __('messages.public_profile_retrieved')
        );
    }

    public function store(StorePublicProfileRequest $request)
    {
        $existing = $this->repository->findForUser($request->user()->id);

        if ($existing) {
            return $this->errorResponse(__('messages.public_profile_already_exists'), 409);
        }

        $validated = $request->validated();
        $mappedData = $this->dataMapper->mapUserDataToPublicProfile($request->input('user_data', []));

        $templateId = $validated['public_profile_template_id']
            ?? $this->repository->getDefaultTemplate()?->id;

        $profile = $this->repository->create(array_merge([
            'user_id' => $request->user()->id,
            'slug' => $validated['slug'] ?? null,
            'language' => $validated['language'] ?? 'en',
            'is_public' => $validated['is_public'] ?? true,
            'headline' => $validated['headline'] ?? null,
            'about' => $validated['about'] ?? null,
            'sections_order' => $validated['sections_order'] ?? null,
            'public_profile_template_id' => $templateId,
        ], $mappedData));

        return $this->successResponse(
            $this->dataMapper->formatPublicProfileResponse($profile),
            __('messages.public_profile_created'),
            201
        );
    }

    public function update(UpdatePublicProfileRequest $request)
    {
        $profile = $this->repository->findForUser($request->user()->id);

        if (! $profile) {
            return $this->errorResponse(__('messages.public_profile_not_found'), 404);
        }

        $validated = $request->validated();
        $updateData = [];

        foreach (['slug', 'language', 'is_public', 'headline', 'about', 'sections_order', 'public_profile_template_id'] as $field) {
            if (array_key_exists($field, $validated)) {
                $updateData[$field] = $validated[$field];
            }
        }

        if (isset($validated['user_data'])) {
            $mappedData = $this->dataMapper->mapUserDataToPublicProfile($validated['user_data']);
            $updateData = array_merge($updateData, $mappedData);
        }

        $updated = $this->repository->update($profile, $updateData);

        return $this->successResponse(
            $this->dataMapper->formatPublicProfileResponse($updated),
            __('messages.public_profile_updated')
        );
    }

    public function destroy(Request $request)
    {
        $profile = $this->repository->findForUser($request->user()->id);

        if (! $profile) {
            return $this->errorResponse(__('messages.public_profile_not_found'), 404);
        }

        $this->repository->delete($profile);

        return $this->successResponse(null, __('messages.public_profile_deleted'));
    }

    public function templates()
    {
        $templates = $this->repository->getActiveTemplates()->map(fn ($t) => [
            'id' => $t->id,
            'name' => $t->name,
            'preview' => $t->preview_url,
            'description' => $t->description,
            'is_default' => $t->is_default,
        ]);

        return $this->successResponse($templates, __('messages.templates_retrieved'));
    }
}
