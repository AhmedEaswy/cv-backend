<?php

namespace App\Filament\Resources\CoverLetters\RelationManagers;

use App\Filament\Resources\CoverLetters\CoverLetterResource;
use App\Models\CoverLetter;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class SiblingCoverLettersRelationManager extends RelationManager
{
    protected static string $relationship = 'siblingCoverLetters';

    protected static ?string $relatedResource = CoverLetterResource::class;

    protected static ?string $title = 'Cover letters from this install';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return filled($ownerRecord->anonymous_user_id);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
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
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->headerActions([])
            ->recordActions([
                ViewAction::make()
                    ->url(fn (CoverLetter $record): string => CoverLetterResource::getUrl('view', ['record' => $record])),
            ]);
    }
}
