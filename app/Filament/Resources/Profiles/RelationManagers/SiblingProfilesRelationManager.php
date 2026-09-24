<?php

namespace App\Filament\Resources\Profiles\RelationManagers;

use App\Filament\Resources\Profiles\ProfileResource;
use App\Models\Profile;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class SiblingProfilesRelationManager extends RelationManager
{
    protected static string $relationship = 'siblingProfiles';

    protected static ?string $relatedResource = ProfileResource::class;

    protected static ?string $title = 'Profiles from this install';

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
                    ->label('Profile ID')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('CV Name')
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
                    ->url(fn (Profile $record): string => ProfileResource::getUrl('view', ['record' => $record])),
            ]);
    }
}
