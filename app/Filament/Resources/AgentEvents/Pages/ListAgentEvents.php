<?php

namespace App\Filament\Resources\AgentEvents\Pages;

use App\Filament\Resources\AgentEvents\AgentEventResource;
use Filament\Resources\Pages\ListRecords;

class ListAgentEvents extends ListRecords
{
    protected static string $resource = AgentEventResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
