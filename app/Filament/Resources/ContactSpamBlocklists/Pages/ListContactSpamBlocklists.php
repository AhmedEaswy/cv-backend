<?php

namespace App\Filament\Resources\ContactSpamBlocklists\Pages;

use App\Filament\Resources\ContactSpamBlocklists\ContactSpamBlocklistResource;
use Filament\Resources\Pages\ListRecords;

class ListContactSpamBlocklists extends ListRecords
{
    protected static string $resource = ContactSpamBlocklistResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
