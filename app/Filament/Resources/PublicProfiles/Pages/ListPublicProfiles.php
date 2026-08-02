<?php

namespace App\Filament\Resources\PublicProfiles\Pages;

use App\Filament\Concerns\HasConfirmableStatusToggles;
use App\Filament\Resources\PublicProfiles\PublicProfileResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPublicProfiles extends ListRecords
{
    use HasConfirmableStatusToggles;

    protected static string $resource = PublicProfileResource::class;

    protected function getHeaderActions(): array
    {
        return $this->withConfirmableStatusToggleAction([
            CreateAction::make(),
        ]);
    }
}
