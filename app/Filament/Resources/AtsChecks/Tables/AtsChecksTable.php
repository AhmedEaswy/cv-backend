<?php

namespace App\Filament\Resources\AtsChecks\Tables;

use App\Filament\Resources\AtsChecks\AtsCheckResource;
use App\Filament\Resources\Profiles\ProfileResource;
use App\Filament\Resources\Users\UserResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class AtsChecksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('score')
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state >= 85 => 'success',
                        $state >= 70 => 'info',
                        $state >= 55 => 'warning',
                        default => 'danger',
                    }),
                TextColumn::make('grade')
                    ->badge()
                    ->sortable()
                    ->color(fn (string $state): string => match ($state) {
                        'A' => 'success',
                        'B' => 'info',
                        'C' => 'warning',
                        'D' => 'gray',
                        default => 'danger',
                    }),
                TextColumn::make('source')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => strtoupper($state))
                    ->color(fn (string $state): string => $state === 'pdf' ? 'warning' : 'primary'),
                TextColumn::make('candidate_name')
                    ->label('Candidate')
                    ->searchable()
                    ->placeholder('—'),
                TextColumn::make('candidate_email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable()
                    ->placeholder('—'),
                TextColumn::make('user.name')
                    ->label('User')
                    ->url(fn ($record) => $record->user_id
                        ? UserResource::getUrl('view', ['record' => $record->user_id])
                        : null)
                    ->placeholder(__('Anonymous'))
                    ->toggleable(),
                TextColumn::make('profile.name')
                    ->label('CV')
                    ->url(fn ($record) => $record->profile_id
                        ? ProfileResource::getUrl('view', ['record' => $record->profile_id])
                        : null)
                    ->placeholder('—')
                    ->toggleable(),
                IconColumn::make('has_job_description')
                    ->label('JD')
                    ->boolean(),
                TextColumn::make('keyword_coverage')
                    ->label('Keyword %')
                    ->suffix('%')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('country')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('—'),
                TextColumn::make('created_at')
                    ->label('Checked At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('grade')
                    ->options([
                        'A' => 'A',
                        'B' => 'B',
                        'C' => 'C',
                        'D' => 'D',
                        'F' => 'F',
                    ]),
                SelectFilter::make('source')
                    ->options([
                        'structured' => 'Structured',
                        'pdf' => 'PDF',
                    ]),
                TernaryFilter::make('has_job_description')
                    ->label('With job description'),
            ])
            ->recordUrl(fn ($record) => AtsCheckResource::getUrl('view', ['record' => $record]))
            ->actions([
                ViewAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
