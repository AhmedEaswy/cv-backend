<?php

namespace App\Filament\Resources\AnalyticsEvents\Tables;

use App\Filament\Resources\AnalyticsEvents\AnalyticsEventResource;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnalyticsEventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label(__('When'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('action_type')
                    ->searchable()
                    ->placeholder('—'),
                TextColumn::make('app_platform')
                    ->label(__('Platform'))
                    ->badge()
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('os')
                    ->toggleable()
                    ->placeholder('—'),
                TextColumn::make('os_version')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('—'),
                TextColumn::make('device_model')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('—'),
                TextColumn::make('country')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('anonymous_user_id')
                    ->label(__('Anonymous ID'))
                    ->limit(12)
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                TextColumn::make('user.name')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('endpoint')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('method')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('response_status')
                    ->label('HTTP')
                    ->sortable(),
                IconColumn::make('is_agent')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('action_type')
                    ->options(fn () => AnalyticsEventResource::getEloquentQuery()
                        ->whereNotNull('action_type')
                        ->distinct()
                        ->orderBy('action_type')
                        ->limit(100)
                        ->pluck('action_type', 'action_type')
                        ->all()),
                SelectFilter::make('app_platform')
                    ->options([
                        'ios' => 'iOS',
                        'android' => 'Android',
                        'web' => 'Web',
                        'api' => 'API',
                    ]),
                SelectFilter::make('os')
                    ->options(fn () => AnalyticsEventResource::getEloquentQuery()
                        ->whereNotNull('os')
                        ->distinct()
                        ->orderBy('os')
                        ->pluck('os', 'os')
                        ->all()),
                SelectFilter::make('country')
                    ->options(fn () => AnalyticsEventResource::getEloquentQuery()
                        ->whereNotNull('country')
                        ->distinct()
                        ->orderBy('country')
                        ->limit(100)
                        ->pluck('country', 'country')
                        ->all()),
                Filter::make('has_anonymous')
                    ->label(__('Guest install only'))
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('anonymous_user_id')),
            ])
            ->recordUrl(fn ($record) => AnalyticsEventResource::getUrl('view', ['record' => $record]))
            ->headerActions([
                Action::make('export')
                    ->label(__('Export CSV'))
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
        $filename = 'analytics-events-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'id', 'created_at', 'action_type', 'app_platform', 'app_version',
                'device_type', 'os', 'os_version', 'device_model', 'browser', 'locale',
                'country', 'anonymous_user_id', 'user_id', 'endpoint', 'method',
                'response_status', 'duration_ms',
            ]);

            $query->orderBy('id')->chunk(500, function ($rows) use ($handle) {
                foreach ($rows as $row) {
                    fputcsv($handle, [
                        $row->id,
                        $row->created_at,
                        $row->action_type,
                        $row->app_platform,
                        $row->app_version,
                        $row->device_type,
                        $row->os,
                        $row->os_version,
                        $row->device_model,
                        $row->browser,
                        $row->locale,
                        $row->country,
                        $row->anonymous_user_id,
                        $row->user_id,
                        $row->endpoint,
                        $row->method,
                        $row->response_status,
                        $row->duration_ms,
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
