<?php

namespace App\Filament\Resources\ProductTours\RelationManagers;

use App\Filament\Support\TranslatableInputs;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StepsRelationManager extends RelationManager
{
    protected static string $relationship = 'steps';

    protected static ?string $title = 'Steps';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('sort_order')->numeric()->default(0)->required(),
            Toggle::make('is_enabled')->default(true),
            TranslatableInputs::fieldset('title', 'body'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('sort_order')->sortable(),
                TextColumn::make('title')->formatStateUsing(fn ($record) => $record->translation('title')),
                IconColumn::make('is_enabled')->boolean(),
            ])
            ->headerActions([CreateAction::make()])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
