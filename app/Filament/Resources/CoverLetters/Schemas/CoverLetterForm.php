<?php

namespace App\Filament\Resources\CoverLetters\Schemas;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CoverLetterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Select::make('cover_letter_template_id')
                    ->relationship(
                        'template',
                        'name',
                        fn ($query) => $query->where('is_active', true)
                    )
                    ->label('Template')
                    ->helperText('Select a template for this cover letter.')
                    ->searchable()
                    ->preload(),
                Checkbox::make('is_public')
                    ->label('Public')
                    ->helperText('Make this cover letter publicly accessible')
                    ->default(false),
                TextInput::make('name')
                    ->label('Cover Letter Name')
                    ->required(),
                Select::make('language')
                    ->label('Language')
                    ->options([
                        'en' => 'English',
                        'ar' => 'Arabic',
                        'tr' => 'Turkish',
                    ])
                    ->default('en')
                    ->required(),

                KeyValue::make('info')
                    ->label('Cover Letter Information')
                    ->addActionLabel('Add Field')
                    ->reorderable()
                    ->keyLabel('Field')
                    ->valueLabel('Value')
                    ->keyPlaceholder('e.g., firstName, lastName, email')
                    ->valuePlaceholder('Enter value')
                    ->columnSpanFull(),

                Repeater::make('experiences')
                    ->label('Experiences')
                    ->schema([
                        TextInput::make('position')
                            ->label('Position')
                            ->required(),
                        TextInput::make('company')
                            ->label('Company')
                            ->required(),
                        Textarea::make('description')
                            ->label('Description')
                            ->rows(3),
                    ])
                    ->addActionLabel('Add Experience')
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => $state['position'] ?? null)
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
