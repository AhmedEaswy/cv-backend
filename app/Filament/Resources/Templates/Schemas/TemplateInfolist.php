<?php

namespace App\Filament\Resources\Templates\Schemas;

use App\Filament\Support\ImagePlaceholder;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TemplateInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Template details'))
                    ->schema([
                        TextEntry::make('name')
                            ->weight('bold'),
                        TextEntry::make('description')
                            ->placeholder('—')
                            ->columnSpanFull(),
                        ImageEntry::make('preview')
                            ->label('Preview Image')
                            ->disk('public')
                            ->height(200)
                            ->defaultImageUrl(ImagePlaceholder::url())
                            ->extraImgAttributes(ImagePlaceholder::imgAttributes())
                            ->columnSpanFull(),
                        IconEntry::make('is_active')
                            ->label('Active')
                            ->boolean(),
                        IconEntry::make('is_default')
                            ->label('Default')
                            ->boolean(),
                        TextEntry::make('created_at')
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->dateTime(),
                    ])
                    ->columns(2),
            ]);
    }
}
