<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\UserType;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Identity'))
                    ->schema([
                        TextEntry::make('name')
                            ->weight('bold'),
                        TextEntry::make('first_name')
                            ->placeholder('—'),
                        TextEntry::make('last_name')
                            ->placeholder('—'),
                        TextEntry::make('type')
                            ->badge()
                            ->formatStateUsing(fn (?UserType $state): ?string => $state?->label())
                            ->color(fn (?UserType $state): string => match ($state) {
                                UserType::ADMIN => 'danger',
                                UserType::USER => 'info',
                                default => 'gray',
                            }),
                    ])
                    ->columns(2),
                Section::make(__('Contact'))
                    ->schema([
                        TextEntry::make('email')
                            ->label('Email address')
                            ->copyable()
                            ->icon('heroicon-m-envelope'),
                        TextEntry::make('phone')
                            ->placeholder('—')
                            ->icon('heroicon-m-phone'),
                    ])
                    ->columns(2),
                Section::make(__('Account status'))
                    ->schema([
                        IconEntry::make('active')
                            ->label('Active')
                            ->boolean(),
                        TextEntry::make('email_verified_at')
                            ->label('Email verified')
                            ->dateTime()
                            ->placeholder(__('Not verified')),
                        TextEntry::make('created_at')
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->dateTime(),
                    ])
                    ->columns(2),
            ]);
    }
}
