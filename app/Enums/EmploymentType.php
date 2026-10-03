<?php

namespace App\Enums;

enum EmploymentType: string
{
    case FULL_TIME = 'full_time';
    case PART_TIME = 'part_time';
    case SELF_EMPLOYED = 'self_employed';
    case FREELANCE = 'freelance';
    case CONTRACT = 'contract';
    case INTERNSHIP = 'internship';
    case APPRENTICESHIP = 'apprenticeship';
    case SEASONAL = 'seasonal';

    public function label(): string
    {
        return match ($this) {
            self::FULL_TIME => 'Full-time',
            self::PART_TIME => 'Part-time',
            self::SELF_EMPLOYED => 'Self-employed',
            self::FREELANCE => 'Freelance',
            self::CONTRACT => 'Contract',
            self::INTERNSHIP => 'Internship',
            self::APPRENTICESHIP => 'Apprenticeship',
            self::SEASONAL => 'Seasonal',
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
