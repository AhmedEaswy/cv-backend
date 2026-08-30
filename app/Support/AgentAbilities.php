<?php

namespace App\Support;

final class AgentAbilities
{
    public const CV_READ = 'cv:read';

    public const CV_WRITE = 'cv:write';

    public const ATS_CHECK = 'ats:check';

    public const COVER_LETTER_READ = 'cover-letter:read';

    public const COVER_LETTER_WRITE = 'cover-letter:write';

    public const PROFILE_READ = 'profile:read';

    public const PROFILE_WRITE = 'profile:write';

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            self::CV_READ,
            self::CV_WRITE,
            self::ATS_CHECK,
            self::COVER_LETTER_READ,
            self::COVER_LETTER_WRITE,
            self::PROFILE_READ,
            self::PROFILE_WRITE,
        ];
    }

    public const TOKEN_NAME_PREFIX = 'agent:';
}
