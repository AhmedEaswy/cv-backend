<?php

namespace App\Services\Auth;

use RuntimeException;

class SocialLinkException extends RuntimeException
{
    public const TAKEN = 'taken';

    public const ALREADY_LINKED = 'already_linked';

    public function __construct(public readonly string $reason)
    {
        parent::__construct($reason);
    }
}
