<?php

namespace App\Filament\Resources\ProductTours\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductTourForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('key')
                ->required()
                ->maxLength(64)
                ->unique(ignoreRecord: true)
                ->helperText(__('Stable identifier used by the portal API')),
            Toggle::make('is_enabled')->default(false),
            TextInput::make('sort_order')->numeric()->default(0)->required(),
        ]);
    }
}
