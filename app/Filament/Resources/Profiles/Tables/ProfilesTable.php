<?php

namespace App\Filament\Resources\Profiles\Tables;

use App\Models\Profile;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProfilesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Profile ID')
                    ->numeric()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('User Name')
                    ->numeric()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('name')
                    ->label('CV Name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('language')
                    ->label('Language')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'en' => 'success',
                        'ar' => 'info',
                        'tr' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('email')
                    ->label('Email')
                    ->getStateUsing(function ($record) {
                        return $record->info['email'] ?? null;
                    })
                    ->searchable(query: function ($query, $search) {
                        return $query->whereRaw("JSON_EXTRACT(info, '$.email') LIKE ?", ["%{$search}%"]);
                    })
                    ->sortable(query: function ($query, string $direction) {
                        return $query->orderByRaw("JSON_EXTRACT(info, '$.email') {$direction}");
                    }),
                TextColumn::make('template.name')
                    ->label('Template')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('info'),
                IconColumn::make('is_public')
                    ->label('Public')
                    ->boolean()
                    ->trueIcon('heroicon-o-globe-alt')
                    ->falseIcon('heroicon-o-lock-closed')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('deleted_at')
                    ->label('Deleted At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('country')
                    ->label('Country')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('device')
                    ->label('Device')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->limit(30),
            ])
            ->filters([
                TrashedFilter::make(),
                TernaryFilter::make('is_public')
                    ->label('Public Status'),
                Filter::make('all')
                    ->label('All')
                    ->query(fn (Builder $query): Builder => $query),
                Filter::make('user')
                    ->label('User')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('user_id')),
                Filter::make('anonymous')
                    ->label('Anonymous')
                    ->query(fn (Builder $query): Builder => $query->whereNull('user_id')),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('preview')
                    ->label(__('Preview'))
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(function (Profile $record): string {
                        $url = route('profile.preview', ['id' => $record->id]);

                        if ($record->template_id) {
                            $url .= '?template_id='.$record->template_id;
                        }

                        return $url;
                    })
                    ->openUrlInNewTab(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
