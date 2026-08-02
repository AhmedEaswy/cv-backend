<?php

namespace App\Filament\Resources\AtsChecks\Pages;

use App\Filament\Resources\AtsChecks\AtsCheckResource;
use Filament\Resources\Pages\ListRecords;

class ListAtsChecks extends ListRecords
{
    protected static string $resource = AtsCheckResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
