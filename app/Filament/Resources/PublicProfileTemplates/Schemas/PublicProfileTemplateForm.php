<?php

namespace App\Filament\Resources\PublicProfileTemplates\Schemas;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PublicProfileTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->helperText('Must match the Blade view name (kebab-case).'),
                FileUpload::make('preview')
                    ->image()
                    ->disk('public')
                    ->directory('public-profile-templates')
                    ->visibility('public')
                    ->imagePreviewHeight('250')
                    ->columnSpanFull(),
                Textarea::make('description')
                    ->rows(3)
                    ->columnSpanFull(),
                Checkbox::make('is_active')
                    ->default(true),
                Checkbox::make('is_default')
                    ->default(false)
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            \App\Models\PublicProfileTemplate::query()
                                ->when(
                                    request()->route('record'),
                                    fn ($q, $record) => $q->where('id', '!=', $record)
                                )
                                ->update(['is_default' => false]);
                        }
                    }),
            ]);
    }
}
