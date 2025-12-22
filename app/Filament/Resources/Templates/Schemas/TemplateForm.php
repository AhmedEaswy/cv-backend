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
                Checkbox::make('is_default')
                    ->label('Default Template')
                    ->helperText('Only one template should be set as default. Setting this will unset other default templates.')
                    ->default(false)
                    ->afterStateUpdated(function ($state, $set, $get, $record) {
                        if ($state) {
                            // Unset other default templates
                            $query = \App\Models\Template::where('is_default', true);
                            if ($record) {
                                $query->where('id', '!=', $record->id);
                            }
                            $query->update(['is_default' => false]);
                        }
                    }),
            ]);
    }
}
