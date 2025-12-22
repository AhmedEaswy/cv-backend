<?php

namespace App\Filament\Resources\Profiles\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

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
            ])
            ->filters([
                TrashedFilter::make(),
                TernaryFilter::make('is_public')
                    ->label('Public Status'),
            ])
            ->recordActions([
                ViewAction::make(),
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
