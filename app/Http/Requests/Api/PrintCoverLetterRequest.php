<?php

namespace App\Http\Requests\Api;

class PrintCoverLetterRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cover_letter_id' => 'sometimes|nullable|exists:cover_letters,id',
            'template_id' => 'required|exists:cover_letter_templates,id',
            'user_data' => 'required_without:cover_letter_id|array',
            'user_data.firstName' => 'required_with:user_data|string|max:255',
            'user_data.lastName' => 'required_with:user_data|string|max:255',
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
        ];
    }
}
