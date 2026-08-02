<?php

namespace App\Filament\Resources\CoverLetterTemplates\RelationManagers;

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

    protected static ?string $title = 'Cover letters using this template';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
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
