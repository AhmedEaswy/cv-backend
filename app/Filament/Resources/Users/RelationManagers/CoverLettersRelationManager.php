<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Filament\Resources\CoverLetters\CoverLetterResource;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CoverLettersRelationManager extends RelationManager
{
    protected static string $relationship = 'coverLetters';

    protected static ?string $relatedResource = CoverLetterResource::class;

    protected static ?string $title = 'Cover letters';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('language')
                    ->badge()
                    ->sortable(),
                TextColumn::make('template.name')
                    ->label('Template')
                    ->badge()
                    ->color('info')
                    ->placeholder('—'),
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
