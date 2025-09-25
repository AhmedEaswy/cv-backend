<?php

namespace App\Filament\Resources\Templates\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class TemplateInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('preview'),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
                TextEntry::make('deleted_at')
                    ->dateTime(),
            ]);
    }
}
