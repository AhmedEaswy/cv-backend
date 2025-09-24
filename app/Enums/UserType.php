<?php

namespace App\Enums;

enum UserType: string
{
    case ADMIN = 'admin';
    case USER = 'user';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Admin',
            self::USER => 'User',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
