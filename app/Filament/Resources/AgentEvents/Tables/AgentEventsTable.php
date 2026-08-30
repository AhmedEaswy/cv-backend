<?php

namespace App\Filament\Resources\AgentEvents\Tables;

use App\Filament\Resources\AgentEvents\AgentEventResource;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AgentEventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label(__('ai_analytics.when'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('channel')
                    ->badge()
                    ->sortable(),
                TextColumn::make('agent_client')
                    ->label(__('ai_analytics.platform'))
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('tool_name')
                    ->label(__('ai_analytics.tool'))
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('action_type')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('endpoint')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('method')
                    ->badge(),
                TextColumn::make('response_status')
                    ->label('HTTP')
                    ->sortable(),
                TextColumn::make('country')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_agent')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('channel')
                    ->options([
                        'mcp' => 'MCP',
                        'skill' => 'Skill',
                        'api' => 'API',
                        'web' => 'Web',
                    ]),
                SelectFilter::make('agent_client')
                    ->label(__('ai_analytics.platform'))
                    ->options(fn () => AgentEventResource::getEloquentQuery()
                        ->whereNotNull('agent_client')
                        ->distinct()
                        ->orderBy('agent_client')
                        ->pluck('agent_client', 'agent_client')
                        ->all()),
            ])
            ->recordUrl(fn ($record) => AgentEventResource::getUrl('view', ['record' => $record]))
            ->headerActions([
                Action::make('export')
                    ->label(__('ai_analytics.export_csv'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(fn ($livewire) => static::export($livewire->getFilteredTableQuery())),
            ])
            ->actions([
                ViewAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function export(Builder $query): StreamedResponse
    {
        $filename = 'agent-events-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'id', 'created_at', 'channel', 'agent_client', 'tool_name',
                'action_type', 'endpoint', 'method', 'response_status', 'country', 'ip_address',
            ]);

            $query->orderBy('id')->chunk(500, function ($rows) use ($handle) {
                foreach ($rows as $row) {
                    fputcsv($handle, [
                        $row->id,
                        $row->created_at,
                        $row->channel,
                        $row->agent_client,
                        $row->tool_name,
                        $row->action_type,
                        $row->endpoint,
                        $row->method,
                        $row->response_status,
                        $row->country,
                        $row->ip_address,
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
