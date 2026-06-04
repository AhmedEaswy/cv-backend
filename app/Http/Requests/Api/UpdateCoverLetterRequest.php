<?php

namespace App\Http\Requests\Api;

class UpdateCoverLetterRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'language' => 'sometimes|string|max:10|in:en,ar,tr',
            'sections_order' => 'sometimes|array',
            'sections_order.*' => 'string',
            'cover_letter_template_id' => 'sometimes|nullable|exists:cover_letter_templates,id',
            'user_data' => 'sometimes|array',
            'user_data.firstName' => 'sometimes|string|max:255',
            'user_data.lastName' => 'sometimes|string|max:255',
            'user_data.email' => 'sometimes|email|max:255',
            'user_data.phone' => 'sometimes|nullable|string|max:50',
            'user_data.address' => 'sometimes|nullable|string|max:500',
            'user_data.jobTitle' => 'sometimes|string|max:255',
            'user_data.companyName' => 'sometimes|string|max:255',
            'user_data.recipientName' => 'sometimes|string|max:255',
            'user_data.recipientTitle' => 'sometimes|nullable|string|max:255',
            'user_data.recipientCompany' => 'sometimes|nullable|string|max:255',
            'user_data.subject' => 'sometimes|string|max:500',
            'user_data.body' => 'sometimes|string',
            'user_data.closing' => 'sometimes|nullable|string|max:255',
            'user_data.experiences' => 'sometimes|array',
        ];
    }
}
