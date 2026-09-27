<?php

namespace App\Http\Requests\Api\Concerns;

use App\Services\Contact\SocialLinksNormalizer;
use App\Services\Profile\ProfileDomain;
use Illuminate\Validation\Validator;

trait ValidatesPublicProfileFields
{
    /**
     * @return array<string, mixed>
     */
    protected function publicProfileFieldRules(?int $profileId = null): array
    {
        $slugRule = [
            'sometimes',
            'nullable',
            'string',
            'max:100',
            'regex:/^[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/',
        ];

        if ($profileId) {
            $slugRule[] = \Illuminate\Validation\Rule::unique('public_profiles', 'slug')
                ->ignore($profileId)
                ->whereNull('deleted_at');
        } else {
            $slugRule[] = 'unique:public_profiles,slug';
        }

        return [
            'slug' => $slugRule,
            'language' => 'sometimes|string|max:10|in:en,ar,tr,es,fr,de,ur',
            'is_public' => 'sometimes|boolean',
            'enable_contact_form' => 'sometimes|boolean',
            'enable_subdomain' => 'sometimes|boolean',
            'contact_form_recipient' => 'sometimes|nullable|email:rfc|max:191',
            'headline' => 'sometimes|nullable|string|max:255',
            'about' => 'sometimes|nullable|string',
            'sections_order' => 'sometimes|array',
            'sections_order.*' => 'string',
            'public_profile_template_id' => [
                'sometimes',
                'nullable',
                'exists:public_profile_templates,id',
            ],
            'user_data' => 'sometimes|array',
            'user_data.firstName' => 'sometimes|string|max:255',
            'user_data.lastName' => 'sometimes|string|max:255',
            'user_data.jobTitle' => 'sometimes|nullable|string|max:255',
            'user_data.email' => 'sometimes|nullable|email|max:255',
            'user_data.phone' => 'sometimes|nullable|string|max:50',
            'user_data.address' => 'sometimes|nullable|string|max:500',
            'user_data.city' => 'sometimes|nullable|string|max:255',
            'user_data.country' => 'sometimes|nullable|string|max:255',
            'user_data.photo' => 'sometimes|nullable|string|max:1000',
            'user_data.coverImage' => 'sometimes|nullable|string|max:1000',
            'user_data.website' => 'sometimes|nullable|string|max:500',
            'user_data.birthdate' => 'sometimes|nullable|string|max:50',
            'user_data.pronouns' => 'sometimes|nullable|string|max:50',
            'user_data.socialLinks' => 'sometimes|array',
            'user_data.socialLinks.*' => 'sometimes',
            'user_data.socialLinks.*.platform' => 'sometimes|string|max:32',
            'user_data.socialLinks.*.url' => 'sometimes|nullable|string|max:500',
            'user_data.socialLinks.*.label' => 'sometimes|nullable|string|max:120',
            'user_data.experiences' => 'sometimes|array',
            'user_data.educations' => 'sometimes|array',
            'user_data.projects' => 'sometimes|array',
            'user_data.skills' => 'sometimes|array',
            'user_data.languages' => 'sometimes|array',
            'user_data.services' => 'sometimes|array',
            'user_data.testimonials' => 'sometimes|array',
            'user_data.certifications' => 'sometimes|array',
            'user_data.achievements' => 'sometimes|array',
            'user_data.availability' => 'sometimes|nullable|array',
            'user_data.cta' => 'sometimes|nullable|array',
            'user_data.seo' => 'sometimes|nullable|array',
            'user_data.seo.meta_title' => 'sometimes|nullable|string|max:120',
            'user_data.seo.meta_description' => 'sometimes|nullable|string|max:320',
            'user_data.seo.og_image' => 'sometimes|nullable|string|max:1000',
            'user_data.seo.robots' => 'sometimes|nullable|string|max:80',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $profileDomain = app(ProfileDomain::class);
            $slug = $this->input('slug');

            if ($slug === null && $this->user()?->publicProfile) {
                $slug = $this->user()->publicProfile->slug;
            }

            $enableSubdomain = $this->has('enable_subdomain')
                ? $this->boolean('enable_subdomain')
                : (bool) ($this->user()?->publicProfile?->enable_subdomain ?? false);

            if (is_string($slug) && $slug !== '') {
                if ($profileDomain->isReserved($slug)) {
                    $v->errors()->add('slug', __('messages.slug_reserved'));
                }

                if ($enableSubdomain && $profileDomain->isReserved($slug)) {
                    $v->errors()->add('enable_subdomain', __('messages.subdomain_slug_reserved'));
                }
            }

            $links = $this->input('user_data.socialLinks');
            if (! is_array($links)) {
                return;
            }

            $normalized = app(SocialLinksNormalizer::class)->normalize($links);
            foreach ($normalized as $index => $link) {
                if (empty($link['url']) || ! filter_var($link['url'], FILTER_VALIDATE_URL)) {
                    $v->errors()->add(
                        "user_data.socialLinks.{$index}.url",
                        __('validation.url', ['attribute' => 'url'])
                    );
                }
                if (($link['platform'] ?? '') === 'custom' && blank($link['label'] ?? null)) {
                    $v->errors()->add(
                        "user_data.socialLinks.{$index}.label",
                        __('messages.social_custom_label_required')
                    );
                }
            }
        });
    }
}