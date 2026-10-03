<?php

namespace App\Filament\Resources\ContactMessages\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ContactMessageInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('publicProfile.slug')->label(__('Profile')),
                TextEntry::make('name'),
                TextEntry::make('email'),
                TextEntry::make('subject')->placeholder('—'),
                TextEntry::make('message')
                    ->label(__('Message'))
                    ->columnSpanFull()
                    ->prose(),
                TextEntry::make('moderation_status')->badge(),
                TextEntry::make('ip_address')->label(__('IP address')),
                TextEntry::make('created_at')->dateTime(),
            ]);
    }
}
