<?php

namespace App\Services;

use App\Models\PublicProfile;
use App\Services\Contact\SocialLinksNormalizer;

class PublicProfileDataMapper
{
    public function __construct(
        private SocialLinksNormalizer $socialLinksNormalizer,
    ) {
    }

    public function mapUserDataToPublicProfile(array $userData): array
    {
        $mapped = [];

        $infoKeys = [
            'firstName', 'lastName', 'jobTitle', 'email', 'phone', 'address',
            'city', 'country', 'photo', 'coverImage', 'website', 'birthdate', 'pronouns',
        ];

        $info = [];
        foreach ($infoKeys as $key) {
            if (array_key_exists($key, $userData)) {
                $info[$key] = $userData[$key];
            }
        }
        if ($info) {
            $mapped['info'] = $info;
        }

        $listKeys = [
            'social_links' => 'socialLinks',
            'experiences' => 'experiences',
            'educations' => 'educations',
            'projects' => 'projects',
            'skills' => 'skills',
            'languages' => 'languages',
            'services' => 'services',
            'testimonials' => 'testimonials',
            'certifications' => 'certifications',
            'achievements' => 'achievements',
            'availability' => 'availability',
            'cta' => 'cta',
            'seo' => 'seo',
        ];

        foreach ($listKeys as $dbKey => $apiKey) {
            if (array_key_exists($apiKey, $userData)) {
                $mapped[$dbKey] = $userData[$apiKey];
            } elseif (array_key_exists($dbKey, $userData)) {
                $mapped[$dbKey] = $userData[$dbKey];
            }
        }

        if (array_key_exists('social_links', $mapped)) {
            $mapped['social_links'] = $this->socialLinksNormalizer->normalize($mapped['social_links']);
        }

        return $mapped;
    }

    public function formatPublicProfileResponse(PublicProfile $profile): array
    {
        $info = $profile->info ?? [];

        $socialLinks = $this->socialLinksNormalizer->normalize($profile->social_links ?? []);

        $userData = array_merge($info, [
            'socialLinks' => $socialLinks,
            'experiences' => $profile->experiences ?? [],
            'educations' => $profile->educations ?? [],
            'projects' => $profile->projects ?? [],
            'skills' => $profile->skills ?? [],
            'languages' => $profile->languages ?? [],
            'services' => $profile->services ?? [],
            'testimonials' => $profile->testimonials ?? [],
            'certifications' => $profile->certifications ?? [],
            'achievements' => $profile->achievements ?? [],
            'availability' => $profile->availability ?? null,
            'cta' => $profile->cta ?? null,
            'seo' => $profile->seo ?? null,
        ]);

        return [
            'id' => $profile->id,
            'user_id' => $profile->user_id,
            'slug' => $profile->slug,
            'public_url' => $profile->preferredPublicUrl(),
            'path_url' => $profile->pathUrl(),
            'subdomain_url' => $profile->subdomainUrl(),
            'is_public' => $profile->is_public,
            'enable_contact_form' => (bool) $profile->enable_contact_form,
            'enable_subdomain' => (bool) $profile->enable_subdomain,
            'contact_form_recipient' => $profile->contact_form_recipient,
            'language' => $profile->language,
            'headline' => $profile->headline,
            'about' => $profile->about,
            'sections_order' => $profile->sections_order,
            'public_profile_template_id' => $profile->public_profile_template_id,
            'user_data' => $userData,
            'created_at' => $profile->created_at?->toIso8601String(),
            'updated_at' => $profile->updated_at?->toIso8601String(),
        ];
    }
}
