<?php

namespace App\Filament\Resources\PublicProfileTemplates\Pages;

use App\Filament\Concerns\HasConfirmableStatusToggles;
use App\Filament\Resources\PublicProfileTemplates\PublicProfileTemplateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPublicProfileTemplates extends ListRecords
{
    use HasConfirmableStatusToggles;

    protected static string $resource = PublicProfileTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return $this->withConfirmableStatusToggleAction([
            CreateAction::make(),
        ]);
    }
}
