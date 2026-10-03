<?php

namespace App\Filament\Resources\HelpArticles\Schemas;

use App\Enums\HelpArticleKind;
use App\Filament\Support\TranslatableInputs;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class HelpArticleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('kind')
                ->options([
                    HelpArticleKind::Faq->value => __('FAQ'),
                    HelpArticleKind::Guide->value => __('Guide'),
                ])
                ->required()
                ->native(false),
            TextInput::make('category')
                ->maxLength(120),
            TextInput::make('slug')
                ->required()
                ->maxLength(191)
                ->unique(ignoreRecord: true)
                ->helperText(__('Used in public URLs'))
                ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug((string) $state))),
            Toggle::make('is_published')
                ->default(false),
            TextInput::make('sort_order')
                ->numeric()
                ->default(0)
                ->required(),
            TranslatableInputs::fieldset('title', 'body'),
        ]);
    }
}
