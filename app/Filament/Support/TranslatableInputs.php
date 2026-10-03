<?php

namespace App\Filament\Support;

use App\Support\AppLocale;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;

final class TranslatableInputs
{
    /**
     * @return list<Tab>
     */
    public static function tabs(string $titleField = 'title', string $bodyField = 'body'): array
    {
        $tabs = [];
        foreach (AppLocale::SUPPORTED as $locale) {
            $tabs[] = Tab::make(strtoupper($locale))
                ->schema([
                    TextInput::make("{$titleField}.{$locale}")
                        ->label(__('Title'))
                        ->required($locale === 'en')
                        ->maxLength(500),
                    Textarea::make("{$bodyField}.{$locale}")
                        ->label(__('Body'))
                        ->required($locale === 'en')
                        ->rows(8)
                        ->columnSpanFull(),
                ]);
        }

        return $tabs;
    }

    public static function fieldset(string $titleField = 'title', ?string $bodyField = 'body'): Tabs
    {
        return Tabs::make('translations')
            ->tabs(self::tabs($titleField, $bodyField ?? 'body'))
            ->columnSpanFull();
    }
}
