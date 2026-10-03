<?php

namespace App\Enums;

enum ContactMessageModerationStatus: string
{
    case Approved = 'approved';
    case PendingReview = 'pending_review';
    case RejectedSpam = 'rejected_spam';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
