<?php

namespace App\Filament\Resources\Templates\Pages;

use App\Filament\Concerns\HasConfirmableStatusToggles;
use App\Filament\Resources\Templates\TemplateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTemplates extends ListRecords
{
    use HasConfirmableStatusToggles;

    protected static string $resource = TemplateResource::class;

    protected function getHeaderActions(): array
    {
        return $this->withConfirmableStatusToggleAction([
            CreateAction::make(),
        ]);
    }
}
