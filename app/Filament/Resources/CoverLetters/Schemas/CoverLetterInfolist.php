<?php

namespace App\Filament\Resources\CoverLetters\Schemas;

use App\Filament\Resources\CoverLetterTemplates\CoverLetterTemplateResource;
use App\Filament\Resources\Users\UserResource;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CoverLetterInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Cover letter details'))
                    ->schema([
                        TextEntry::make('id')
                            ->label('ID'),
                        TextEntry::make('name')
                            ->weight('bold'),
                        TextEntry::make('language')
                            ->badge(),
                        IconEntry::make('is_public')
                            ->label('Public')
                            ->boolean(),
                        TextEntry::make('template.name')
                            ->label('Template')
                            ->badge()
                            ->color('info')
                            ->url(fn ($record) => $record->cover_letter_template_id
                                ? CoverLetterTemplateResource::getUrl('view', ['record' => $record->cover_letter_template_id])
                                : null)
                            ->placeholder('—'),
                        TextEntry::make('created_at')
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->dateTime(),
                    ])
                    ->columns(2),
                Section::make(__('Owner'))
                    ->schema([
                        TextEntry::make('user.name')
                            ->label('User')
                            ->url(fn ($record) => $record->user_id
                                ? UserResource::getUrl('view', ['record' => $record->user_id])
                                : null)
                            ->placeholder(__('Anonymous')),
                        TextEntry::make('user.email')
                            ->label('Email')
                            ->icon('heroicon-m-envelope')
                            ->placeholder('—'),
                        TextEntry::make('ip_address')
                            ->placeholder('—'),
                        TextEntry::make('country')
                            ->placeholder('—'),
                    ])
                    ->columns(2),
                Section::make(__('Cover letter information'))
                    ->schema([
                        KeyValueEntry::make('info')
                            ->label('')
                            ->columnSpanFull()
                            ->getStateUsing(fn ($record): array => self::stringifyKeyValueState($record->info)),
                    ])
                    ->columnSpanFull()
                    ->visible(fn ($record): bool => filled($record->info)),
                Section::make(__('Experiences'))
                    ->schema([
                        RepeatableEntry::make('experiences')
                            ->schema([
                                TextEntry::make('position')
                                    ->formatStateUsing(fn ($state): string => self::toDisplayString($state))
                                    ->placeholder('—'),
                                TextEntry::make('company')
                                    ->formatStateUsing(fn ($state): string => self::toDisplayString($state))
                                    ->placeholder('—'),
                                TextEntry::make('description')
                                    ->formatStateUsing(fn ($state): string => self::toDisplayString($state))
                                    ->columnSpanFull()
                                    ->placeholder('—'),
                            ])
                            ->columns(2),
                    ])
                    ->columnSpanFull()
                    ->visible(fn ($record): bool => filled($record->experiences)),
            ]);
    }

    /**
     * @param  mixed  $state
     * @return array<string, string>
     */
    protected static function stringifyKeyValueState(mixed $state): array
    {
        if (! is_array($state)) {
            return [];
        }

        $formatted = [];

        foreach ($state as $key => $value) {
            $formatted[(string) $key] = self::toDisplayString($value);
        }

        return $formatted;
    }

    protected static function toDisplayString(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? __('Yes') : __('No');
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        if (is_array($value)) {
            if (array_is_list($value)) {
                $items = array_map(function (mixed $item): string {
                    if (is_array($item) && isset($item['name'])) {
                        return (string) $item['name'];
                    }

                    return self::toDisplayString($item);
                }, $value);

                return implode(', ', array_filter($items, fn (string $item): bool => $item !== ''));
            }

            return collect($value)
                ->map(fn (mixed $item, mixed $key): string => ((string) $key).': '.self::toDisplayString($item))
                ->implode(', ');
        }

        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '';
    }
}
