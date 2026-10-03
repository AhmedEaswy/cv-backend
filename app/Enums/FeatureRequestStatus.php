<?php

namespace App\Enums;

enum FeatureRequestStatus: string
{
    case UnderReview = 'under_review';
    case Planned = 'planned';
    case InProgress = 'in_progress';
    case Complete = 'complete';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
