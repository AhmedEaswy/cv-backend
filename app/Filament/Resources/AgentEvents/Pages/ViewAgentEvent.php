<?php

namespace App\Filament\Resources\AgentEvents\Pages;

use App\Filament\Resources\AgentEvents\AgentEventResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAgentEvent extends ViewRecord
{
    protected static string $resource = AgentEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
