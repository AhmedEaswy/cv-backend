<?php

namespace App\Http\Requests\Portal;

use Illuminate\Foundation\Http\FormRequest;

class PortalContactMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'string', 'email:rfc', 'max:191'],
            'subject' => ['nullable', 'string', 'max:180'],
            'message' => ['required', 'string', 'min:10', 'max:4000'],
            'hp_check' => ['nullable', 'string', 'max:255'], // honeypot (handled in controller)
            'cf_turnstile_response' => ['nullable', 'string', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [];
    }
}
