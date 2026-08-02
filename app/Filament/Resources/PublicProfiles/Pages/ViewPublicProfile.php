<?php

namespace App\Filament\Resources\PublicProfiles\Pages;

use App\Filament\Resources\PublicProfiles\PublicProfileResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPublicProfile extends ViewRecord
{
    protected static string $resource = PublicProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
