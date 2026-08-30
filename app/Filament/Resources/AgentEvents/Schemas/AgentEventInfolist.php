<?php

namespace App\Filament\Resources\AgentEvents\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AgentEventInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('ai_analytics.event'))
                    ->schema([
                        TextEntry::make('created_at')->dateTime(),
                        TextEntry::make('channel')->badge(),
                        TextEntry::make('agent_client')->placeholder('—'),
                        TextEntry::make('agent_name')->placeholder('—'),
                        TextEntry::make('tool_name')->placeholder('—'),
                        TextEntry::make('action_type')->placeholder('—'),
                        TextEntry::make('endpoint'),
                        TextEntry::make('method')->badge(),
                        TextEntry::make('response_status'),
                        TextEntry::make('duration_ms')->suffix(' ms')->placeholder('—'),
                        TextEntry::make('country')->placeholder('—'),
                        TextEntry::make('device')->placeholder('—'),
                        TextEntry::make('ip_address')->placeholder('—'),
                        TextEntry::make('user.name')->placeholder(__('Anonymous')),
                    ])
                    ->columns(3),
                Section::make(__('ai_analytics.request_data'))
                    ->schema([
                        TextEntry::make('request_data')
                            ->formatStateUsing(fn ($state) => is_array($state)
                                ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
                                : (string) $state),
                    ]),
            ]);
    }
}
