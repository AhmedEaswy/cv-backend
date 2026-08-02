<?php

namespace App\Filament\Resources\PublicProfileTemplates\RelationManagers;

use App\Filament\Resources\PublicProfiles\PublicProfileResource;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PublicProfilesRelationManager extends RelationManager
{
    protected static string $relationship = 'publicProfiles';

    protected static ?string $relatedResource = PublicProfileResource::class;

    protected static ?string $title = 'Profiles using this template';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('slug')
            ->columns([
                TextColumn::make('slug')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->placeholder(__('Anonymous')),
                TextColumn::make('language')
                    ->badge(),
                IconColumn::make('is_public')
                    ->label('Public')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->headerActions([])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ]);
    }
}
