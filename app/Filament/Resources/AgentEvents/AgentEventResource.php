<?php

namespace App\Filament\Resources\AgentEvents;

use App\Filament\Resources\AgentEvents\Pages\ListAgentEvents;
use App\Filament\Resources\AgentEvents\Pages\ViewAgentEvent;
use App\Filament\Resources\AgentEvents\Schemas\AgentEventInfolist;
use App\Filament\Resources\AgentEvents\Tables\AgentEventsTable;
use App\Models\AnalyticsEvent;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class AgentEventResource extends Resource
{
    protected static ?string $model = AnalyticsEvent::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cpu-chip';

    protected static string|UnitEnum|null $navigationGroup = 'AI Analytics';

    protected static ?int $navigationSort = 1;

    protected static bool $hasTitleCaseModelLabel = false;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('AI Analytics');
    }

    public static function getNavigationLabel(): string
    {
        return __('ai_analytics.events');
    }

    public static function getModelLabel(): string
    {
        return __('ai_analytics.event');
    }

    public static function getPluralModelLabel(): string
    {
        return __('ai_analytics.events');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('is_agent', true);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AgentEventInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AgentEventsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAgentEvents::route('/'),
            'view' => ViewAgentEvent::route('/{record}'),
        ];
    }
}
