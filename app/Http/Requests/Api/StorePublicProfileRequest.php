<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\Api\Concerns\ValidatesPublicProfileFields;

class StorePublicProfileRequest extends BaseFormRequest
{
    use ValidatesPublicProfileFields;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return $this->publicProfileFieldRules();
    }
}
