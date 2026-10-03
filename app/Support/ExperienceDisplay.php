<?php

namespace App\Support;

class ExperienceDisplay
{
    /**
     * @param  array<string, mixed>  $exp
     * @return list<string>
     */
    public static function typeLabels(array $exp): array
    {
        $labels = [];

        if (! empty($exp['employmentType'])) {
            $labels[] = __('portal.cvs.employment_type.'.$exp['employmentType']);
        }

        if (! empty($exp['locationType'])) {
            $labels[] = __('portal.cvs.location_type.'.$exp['locationType']);
        }

        return $labels;
    }

    /**
     * @param  array<string, mixed>  $exp
     * @return list<string>
     */
    public static function metaParts(array $exp): array
    {
        return array_values(array_filter([
            $exp['company'] ?? null,
            $exp['location'] ?? null,
            ...self::typeLabels($exp),
        ], fn ($value) => filled($value)));
    }

    /**
     * @param  array<string, mixed>  $exp
     */
    public static function metaLine(array $exp, string $separator = ' · '): string
    {
        return implode($separator, self::metaParts($exp));
    }

    /**
     * @param  array<string, mixed>  $exp
     */
    public static function typesLine(array $exp, string $separator = ' · '): string
    {
        return implode($separator, self::typeLabels($exp));
    }
}
