<?php

namespace App\Filament\Resources\ProductTours\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductToursTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('key')->searchable(),
                IconColumn::make('is_enabled')->boolean(),
                TextColumn::make('steps_count')->counts('steps')->label(__('Steps')),
                TextColumn::make('sort_order')->sortable(),
            ])
            ->recordActions([EditAction::make()]);
    }
}
