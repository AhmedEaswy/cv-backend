<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ResetPasswordRequest extends FormRequest
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
            'code' => ['required', 'string', 'size:6'],
            'email' => ['required', 'string', 'email:rfc', 'max:191'],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)->max(128)->letters()->mixedCase()->numbers(),
            ],
        ];
    }
}
