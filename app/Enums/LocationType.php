<?php

namespace App\Enums;

enum LocationType: string
{
    case ON_SITE = 'on_site';
    case HYBRID = 'hybrid';
    case REMOTE = 'remote';

    public function label(): string
    {
        return match ($this) {
            self::ON_SITE => 'On-site',
            self::HYBRID => 'Hybrid',
            self::REMOTE => 'Remote',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->all();
    }
}
