<?php

namespace App\Filament\Resources\PublicProfiles\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PublicProfileInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Profile'))
                    ->schema([
                        TextEntry::make('id')->label('ID'),
                        TextEntry::make('user.name')->label('User'),
                        TextEntry::make('slug')->copyable(),
                        TextEntry::make('public_url')
                            ->label('Public URL')
                            ->getStateUsing(fn ($record) => $record->public_url)
                            ->url(fn ($record) => $record->public_url)
                            ->openUrlInNewTab(),
                        TextEntry::make('template.name')->label('Template')->placeholder('—'),
                        TextEntry::make('language')->badge(),
                        IconEntry::make('is_public')->label('Public')->boolean(),
                        TextEntry::make('headline')->placeholder('—')->columnSpanFull(),
                        TextEntry::make('about')->placeholder('—')->columnSpanFull(),
                        TextEntry::make('created_at')->dateTime(),
                        TextEntry::make('updated_at')->dateTime(),
                    ])
                    ->columns(2),
            ]);
    }
}
