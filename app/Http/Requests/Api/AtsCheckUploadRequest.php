<?php

namespace App\Http\Requests\Api;

class AtsCheckUploadRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => 'required|file|mimes:pdf|max:5120',
            'job_description' => 'sometimes|nullable|string|max:20000',
            'language' => 'sometimes|string|max:10|in:en,ar,tr',
        ];
    }
}
