<?php

namespace App\Filament\Resources\SupportTickets\Schemas;

use App\Enums\SupportTicketStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SupportTicketForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->disabled(),
            TextInput::make('email')->disabled(),
            TextInput::make('subject')->disabled(),
            Textarea::make('body')->disabled()->rows(10)->columnSpanFull(),
            Select::make('status')
                ->options([
                    SupportTicketStatus::Open->value => __('Open'),
                    SupportTicketStatus::InProgress->value => __('In progress'),
                    SupportTicketStatus::Resolved->value => __('Resolved'),
                    SupportTicketStatus::Closed->value => __('Closed'),
                ])
                ->required()
                ->native(false),
        ]);
    }
}
