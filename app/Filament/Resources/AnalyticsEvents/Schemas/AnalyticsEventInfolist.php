<?php

namespace App\Filament\Resources\AnalyticsEvents\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AnalyticsEventInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Event'))
                    ->schema([
                        TextEntry::make('created_at')->dateTime(),
                        TextEntry::make('action_type')->placeholder('—'),
                        TextEntry::make('endpoint'),
                        TextEntry::make('method')->badge(),
                        TextEntry::make('response_status'),
                        TextEntry::make('duration_ms')->suffix(' ms')->placeholder('—'),
                        TextEntry::make('channel')->badge()->placeholder('—'),
                        TextEntry::make('is_agent')->boolean(),
                    ])
                    ->columns(3),
                Section::make(__('Client'))
                    ->schema([
                        TextEntry::make('app_platform')->placeholder('—'),
                        TextEntry::make('app_version')->placeholder('—'),
                        TextEntry::make('device_type')->placeholder('—'),
                        TextEntry::make('os')->placeholder('—'),
                        TextEntry::make('os_version')->placeholder('—'),
                        TextEntry::make('device_model')->placeholder('—'),
                        TextEntry::make('browser')->placeholder('—'),
                        TextEntry::make('locale')->placeholder('—'),
                        TextEntry::make('device')->placeholder('—')->columnSpanFull(),
                        TextEntry::make('user_agent')->placeholder('—')->columnSpanFull(),
                    ])
                    ->columns(3),
                Section::make(__('Identity'))
                    ->schema([
                        TextEntry::make('user.name')->placeholder(__('Anonymous')),
                        TextEntry::make('anonymous_user_id')->label(__('Anonymous install ID'))->placeholder('—')->copyable(),
                        TextEntry::make('profile_id')->placeholder('—'),
                        TextEntry::make('country')->placeholder('—'),
                        TextEntry::make('ip_address')->placeholder('—'),
                    ])
                    ->columns(3),
                Section::make(__('Meta'))
                    ->schema([
                        TextEntry::make('meta')
                            ->formatStateUsing(fn ($state) => is_array($state)
                                ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
                                : (string) $state)
                            ->placeholder('—'),
                    ]),
                Section::make(__('Request data'))
                    ->schema([
                        TextEntry::make('request_data')
                            ->formatStateUsing(fn ($state) => is_array($state)
                                ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
                                : (string) $state)
                            ->placeholder('—'),
                    ]),
            ]);
    }
}
