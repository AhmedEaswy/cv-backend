<?php

namespace App\Filament\Resources\AtsChecks\Pages;

use App\Filament\Resources\AtsChecks\AtsCheckResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAtsCheck extends ViewRecord
{
    protected static string $resource = AtsCheckResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
