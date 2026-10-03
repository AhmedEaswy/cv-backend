<?php

namespace App\Http\Controllers\Api\Support;

use App\Http\Controllers\Api\BaseApiController;
use App\Services\Contact\TurnstileVerifier;

class SupportMetaController extends BaseApiController
{
    public function __construct(private readonly TurnstileVerifier $turnstile)
    {
    }

    public function contactForm()
    {
        return $this->successResponse([
            'turnstile' => [
                'enabled' => $this->turnstile->isRequired(),
                'site_key' => $this->turnstile->siteKey(),
            ],
        ], __('messages.support_meta_retrieved'));
    }
}
