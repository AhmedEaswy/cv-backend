<?php

namespace App\Http\Requests\Api;

class StorePublicProfileRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'slug' => 'sometimes|nullable|string|max:100|alpha_dash|unique:public_profiles,slug',
            'language' => 'sometimes|string|max:10|in:en,ar,tr',
            'is_public' => 'sometimes|boolean',
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
        ];
    }
}
