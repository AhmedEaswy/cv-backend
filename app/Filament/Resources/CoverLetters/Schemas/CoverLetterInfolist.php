<?php

namespace App\Filament\Resources\CoverLetters\Schemas;

use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CoverLetterInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextEntry::make('id')
                            ->label('ID'),
                        TextEntry::make('user.name')
                            ->label('User'),
                        TextEntry::make('template.name')
                            ->label('Template'),
                        TextEntry::make('name')
                            ->label('Name'),
                        TextEntry::make('language')
                            ->label('Language')
                            ->badge(),
                        TextEntry::make('is_public')
                            ->label('Public')
                            ->badge()
                            ->color(fn (bool $state): string => $state ? 'success' : 'gray')
                            ->formatStateUsing(fn (bool $state): string => $state ? __('Yes') : __('No')),
                        TextEntry::make('created_at')
                            ->label('Created At')
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->label('Updated At')
                            ->dateTime(),
                    ])
                    ->columns(2),
                Section::make(__('Cover Letter Information'))
                    ->schema([
                        KeyValueEntry::make('info')
                            ->label(__('Cover Letter Information')),
                    ])
                    ->visible(fn ($record): bool => filled($record->info)),
                Section::make(__('Experiences'))
                    ->schema([
                        RepeatableEntry::make('experiences')
                            ->schema([
                                TextEntry::make('position')
                                    ->label(__('Position')),
                                TextEntry::make('company')
                                    ->label(__('Company')),
                                TextEntry::make('description')
                                    ->label(__('Description')),
                            ])
                            ->columns(2),
                    ])
                    ->visible(fn ($record): bool => filled($record->experiences)),
            ]);
    }
}
