<?php

namespace App\Filament\Resources\Profiles\Pages;

use App\Filament\Concerns\HasConfirmableStatusToggles;
use App\Filament\Resources\Profiles\ProfileResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProfiles extends ListRecords
{
    use HasConfirmableStatusToggles;

    protected static string $resource = ProfileResource::class;

    protected function getHeaderActions(): array
    {
        return $this->withConfirmableStatusToggleAction([
            CreateAction::make(),
        ]);
    }
}
