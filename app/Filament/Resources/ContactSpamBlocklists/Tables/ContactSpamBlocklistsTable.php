<?php

namespace App\Filament\Resources\ContactSpamBlocklists\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ContactSpamBlocklistsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('email')->searchable()->placeholder('—'),
                TextColumn::make('ip_address')->searchable()->placeholder('—'),
                TextColumn::make('reason')->badge()->sortable(),
                TextColumn::make('reportedBy.name')
                    ->label('Reported by')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('reason')->options([
                    'reported' => 'Reported',
                    'honeypot' => 'Honeypot',
                    'cross_profile' => 'Cross-profile',
                    'manual' => 'Manual',
                ]),
            ])
            ->recordActions([
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
