<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\Api\Concerns\ValidatesPublicProfileFields;

class UpdatePublicProfileRequest extends BaseFormRequest
{
    use ValidatesPublicProfileFields;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $profileId = $this->user()?->publicProfile?->id;

        return $this->publicProfileFieldRules($profileId);
    }
}
