<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\StorePublicProfileRequest;
use App\Http\Requests\Api\UpdatePublicProfileRequest;
use App\Repositories\PublicProfileRepository;
use App\Services\Profile\ProfileDomain;
use App\Services\PublicProfileDataMapper;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
            'enable_contact_form' => $validated['enable_contact_form'] ?? false,
            'enable_inbox' => $validated['enable_inbox'] ?? true,
            'enable_subdomain' => $validated['enable_subdomain'] ?? false,
            'profile_url_mode' => $validated['profile_url_mode'] ?? 'slug',
            'custom_domain' => $validated['custom_domain'] ?? null,
            'contact_form_recipient' => $validated['contact_form_recipient'] ?? null,
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

        foreach ([
            'slug',
            'language',
            'is_public',
            'enable_contact_form',
            'enable_inbox',
            'enable_subdomain',
            'profile_url_mode',
            'custom_domain',
            'contact_form_recipient',
            'headline',
            'about',
            'sections_order',
            'public_profile_template_id',
        ] as $field) {
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

    public function verifyCustomDomainDns(Request $request, ProfileDomain $profileDomain)
    {
        $profile = $this->repository->findForUser($request->user()->id);

        if (! $profile) {
            return $this->errorResponse(__('messages.public_profile_not_found'), 404);
        }

        if ($profile->profileUrlMode() !== 'custom_domain' || ! filled($profile->custom_domain)) {
            return $this->errorResponse(__('messages.custom_domain_required'), 422);
        }

        if (! $profile->custom_domain_dns_token) {
            $profile->custom_domain_dns_token = Str::random(32);
            $profile->save();
        }

        $expected = $profileDomain->customDomainDnsValue((string) $profile->custom_domain_dns_token);
        $domain = strtolower((string) $profile->custom_domain);
        $hosts = [$profileDomain->customDomainDnsHost($domain), $domain];
        $verified = false;

        foreach ($hosts as $host) {
            $records = @dns_get_record($host, DNS_TXT) ?: [];
            foreach ($records as $row) {
                $txt = $row['txt'] ?? '';
                if (is_string($txt) && str_contains($txt, $expected)) {
                    $verified = true;
                    break 2;
                }
            }
        }

        if (! $verified) {
            return $this->errorResponse(__('messages.custom_domain_dns_failed'), 422);
        }

        $profile->forceFill(['custom_domain_verified_at' => now()])->save();

        return $this->successResponse(
            $this->dataMapper->formatPublicProfileResponse($profile->fresh()),
            __('messages.custom_domain_dns_verified')
        );
    }

    public function templates(Request $request)
    {
        $locale = app()->getLocale();

        return $this->paginatedOrAll(
            $this->repository->activeTemplatesQuery(),
            $request,
            fn ($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'preview' => $t->resolvedPreviewUrl($locale),
                'description' => $t->description,
                'is_default' => $t->is_default,
            ],
            __('messages.templates_retrieved')
        );
    }
}
