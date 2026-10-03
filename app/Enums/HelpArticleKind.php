<?php

namespace App\Enums;

enum HelpArticleKind: string
{
    case Faq = 'faq';
    case Guide = 'guide';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
