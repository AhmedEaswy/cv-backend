<?php

namespace App\Filament\Resources\HelpArticles\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HelpArticlesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('kind')->badge(),
                TextColumn::make('slug')->searchable(),
                TextColumn::make('category')->toggleable(),
                TextColumn::make('title')
                    ->label(__('Title'))
                    ->formatStateUsing(fn ($record) => $record->translation('title')),
                IconColumn::make('is_published')->boolean(),
                TextColumn::make('sort_order')->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
