<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;

class UpdateCVRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled in controller
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'language' => 'sometimes|string|max:10|in:en,ar,tr',
            'sections_order' => 'sometimes|array',
            'sections_order.*' => 'string',
            'user_data' => 'sometimes|array',
            'user_data.firstName' => 'sometimes|string|max:255',
            'user_data.lastName' => 'sometimes|string|max:255',
            'user_data.jobTitle' => 'sometimes|string|max:255',
            'user_data.email' => 'sometimes|email|max:255',
            'user_data.address' => 'sometimes|nullable|string|max:500',
            'user_data.portfolioUrl' => 'sometimes|nullable|url|max:500',
            'user_data.phone' => 'sometimes|nullable|string|max:50',
            'user_data.summary' => 'sometimes|nullable|string',
            'user_data.birthdate' => 'sometimes|nullable|date',
            'user_data.skills' => 'sometimes|array',
            'user_data.skills.*.name' => 'required_with:user_data.skills|string|max:255',
            'user_data.educations' => 'sometimes|array',
            'user_data.educations.*.institution' => 'required_with:user_data.educations|string|max:255',
            'user_data.educations.*.degree' => 'required_with:user_data.educations|string|max:255',
            'user_data.educations.*.fieldOfStudy' => 'required_with:user_data.educations|string|max:255',
            'user_data.educations.*.description' => 'sometimes|nullable|string',
            'user_data.educations.*.from' => 'sometimes|nullable|date_format:Y-m',
            'user_data.educations.*.to' => 'sometimes|nullable|date_format:Y-m',
            'user_data.experiences' => 'sometimes|array',
            'user_data.experiences.*.position' => 'required_with:user_data.experiences|string|max:255',
            'user_data.experiences.*.company' => 'sometimes|nullable|string|max:255',
            'user_data.experiences.*.location' => 'sometimes|nullable|string|max:255',
            'user_data.experiences.*.description' => 'sometimes|nullable|string',
            'user_data.experiences.*.from' => 'sometimes|nullable|date_format:Y-m',
            'user_data.experiences.*.to' => 'sometimes|nullable|date_format:Y-m',
            'user_data.experiences.*.current' => 'sometimes|boolean',
            'user_data.projects' => 'sometimes|array',
            'user_data.projects.*.title' => 'required_with:user_data.projects|string|max:255',
            'user_data.projects.*.description' => 'sometimes|nullable|string',
            'user_data.projects.*.technologies' => 'sometimes|nullable|string|max:500',
            'user_data.projects.*.url' => 'sometimes|nullable|url|max:500',
            'user_data.projects.*.from' => 'sometimes|nullable|date_format:Y-m',
            'user_data.projects.*.to' => 'sometimes|nullable|date_format:Y-m',
            'user_data.projects.*.current' => 'sometimes|boolean',
            'user_data.languages' => 'sometimes|array',
            'user_data.languages.*.name' => 'required_with:user_data.languages|string|max:255',
            'user_data.languages.*.proficiencyLevel' => 'required_with:user_data.languages|integer|min:1|max:5',
            'user_data.interests' => 'sometimes|array',
            'user_data.interests.*.name' => 'required_with:user_data.interests|string|max:255',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $userData = $this->input('user_data', []);

            // Validate date ranges for educations
            if (isset($userData['educations']) && is_array($userData['educations'])) {
                foreach ($userData['educations'] as $index => $education) {
                    if (isset($education['from']) && isset($education['to'])) {
                        if (strtotime($education['from']) > strtotime($education['to'])) {
                            $validator->errors()->add(
                                "user_data.educations.{$index}.to",
                                __('messages.date_range_invalid')
                            );
                        }
                    }
                }
            }

            // Validate date ranges for experiences
            if (isset($userData['experiences']) && is_array($userData['experiences'])) {
                foreach ($userData['experiences'] as $index => $experience) {
                    if (isset($experience['from']) && isset($experience['to']) && !($experience['current'] ?? false)) {
                        if (strtotime($experience['from']) > strtotime($experience['to'])) {
                            $validator->errors()->add(
                                "user_data.experiences.{$index}.to",
                                __('messages.date_range_invalid')
                            );
                        }
                    }
                }
            }

            // Validate date ranges for projects
            if (isset($userData['projects']) && is_array($userData['projects'])) {
                foreach ($userData['projects'] as $index => $project) {
                    if (isset($project['from']) && isset($project['to']) && !($project['current'] ?? false)) {
                        if (strtotime($project['from']) > strtotime($project['to'])) {
                            $validator->errors()->add(
                                "user_data.projects.{$index}.to",
                                __('messages.date_range_invalid')
                            );
                        }
                    }
                }
            }
        });
    }
}
