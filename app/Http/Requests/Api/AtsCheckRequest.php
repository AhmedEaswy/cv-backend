<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;

class AtsCheckRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'profile_id' => 'sometimes|nullable|integer|exists:profiles,id',
            'user_data' => 'sometimes|nullable|array',
            'user_data.firstName' => 'sometimes|nullable|string|max:255',
            'user_data.lastName' => 'sometimes|nullable|string|max:255',
            'user_data.jobTitle' => 'sometimes|nullable|string|max:255',
            'user_data.email' => 'sometimes|nullable|email|max:255',
            'user_data.address' => 'sometimes|nullable|string|max:500',
            'user_data.portfolioUrl' => 'sometimes|nullable|url|max:500',
            'user_data.phone' => 'sometimes|nullable|string|max:50',
            'user_data.summary' => 'sometimes|nullable|string',
            'user_data.birthdate' => 'sometimes|nullable|date',
            'user_data.photo' => 'sometimes|nullable|string',
            'user_data.skills' => 'sometimes|array',
            'user_data.skills.*.name' => 'required_with:user_data.skills|string|max:255',
            'user_data.educations' => 'sometimes|array',
            'user_data.experiences' => 'sometimes|array',
            'user_data.projects' => 'sometimes|array',
            'user_data.languages' => 'sometimes|array',
            'user_data.interests' => 'sometimes|array',
            'job_description' => 'sometimes|nullable|string|max:20000',
            'language' => 'sometimes|string|max:10|in:en,ar,tr',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $hasProfile = $this->filled('profile_id');
            $hasUserData = $this->filled('user_data') && is_array($this->input('user_data'));

            if (! $hasProfile && ! $hasUserData) {
                $validator->errors()->add(
                    'user_data',
                    __('ats.errors.profile_or_user_data_required')
                );
            }
        });
    }
}
