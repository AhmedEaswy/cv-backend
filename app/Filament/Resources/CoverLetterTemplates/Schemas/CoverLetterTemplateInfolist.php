<?php

namespace App\Filament\Resources\CoverLetterTemplates\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CoverLetterTemplateInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextEntry::make('id')
                            ->label('ID'),
                        TextEntry::make('name')
                            ->label('Name'),
                        ImageEntry::make('preview')
                            ->label('Preview Image')
                            ->getStateUsing(fn ($record) => $record->preview_url)
                            ->height(200),
                        TextEntry::make('description')
                            ->label('Description'),
                        TextEntry::make('is_active')
                            ->label('Active')
                            ->badge()
                            ->color(fn (bool $state): string => $state ? 'success' : 'danger')
                            ->formatStateUsing(fn (bool $state): string => $state ? __('Active') : __('Inactive')),
                        TextEntry::make('is_default')
                            ->label('Default')
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
            ]);
    }
}
