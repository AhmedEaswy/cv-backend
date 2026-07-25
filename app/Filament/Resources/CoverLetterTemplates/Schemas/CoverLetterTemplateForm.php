<?php

namespace App\Filament\Resources\CoverLetterTemplates\Schemas;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CoverLetterTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                FileUpload::make('preview')
                    ->image()
                    ->disk('public')
                    ->directory('cover-letter-templates')
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
                            \App\Models\CoverLetterTemplate::where('id', '!=', request()->route('record'))
                                ->update(['is_default' => false]);
                        }
                    }),
            ]);
    }
}
