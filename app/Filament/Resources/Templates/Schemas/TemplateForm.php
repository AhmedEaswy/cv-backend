<?php

namespace App\Filament\Resources\Templates\Schemas;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class TemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                FileUpload::make('preview')
                    ->label('Preview Image')
                    ->image()
                    ->imageEditor()
                    ->disk('public')
                    ->directory('templates/previews')
                    ->visibility('public')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                Checkbox::make('is_active')
                    ->label('Active')
                    ->default(true),
            ]);
    }
}
