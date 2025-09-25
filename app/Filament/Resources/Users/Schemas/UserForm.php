<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\UserType;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                DateTimePicker::make('email_verified_at'),
                TextInput::make('password')
                    ->password()
                    ->required(),
                TextInput::make('first_name'),
                TextInput::make('last_name'),
                TextInput::make('phone')
                    ->tel(),
                Select::make('type')
                    ->options(UserType::class),
            ]);
    }
}
