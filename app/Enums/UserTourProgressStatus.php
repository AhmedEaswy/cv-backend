<?php

namespace App\Enums;

enum UserTourProgressStatus: string
{
    case Completed = 'completed';
    case Dismissed = 'dismissed';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
