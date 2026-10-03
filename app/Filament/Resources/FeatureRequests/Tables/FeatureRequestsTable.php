<?php

namespace App\Filament\Resources\FeatureRequests\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FeatureRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('title')->searchable()->limit(60),
                TextColumn::make('status')->badge(),
                TextColumn::make('vote_count')->sortable(),
                IconColumn::make('is_published')->boolean(),
                TextColumn::make('user.email')->label(__('User')),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->recordActions([EditAction::make()]);
    }
}
