<?php

namespace App\Filament\Resources\CoverLetterTemplates\Pages;

use App\Filament\Concerns\HasConfirmableStatusToggles;
use App\Filament\Resources\CoverLetterTemplates\CoverLetterTemplateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCoverLetterTemplates extends ListRecords
{
    use HasConfirmableStatusToggles;

    protected static string $resource = CoverLetterTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return $this->withConfirmableStatusToggleAction([
            CreateAction::make(),
        ]);
    }
}
