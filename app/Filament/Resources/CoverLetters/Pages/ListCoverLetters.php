<?php

namespace App\Filament\Resources\CoverLetters\Pages;

use App\Filament\Concerns\HasConfirmableStatusToggles;
use App\Filament\Resources\CoverLetters\CoverLetterResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCoverLetters extends ListRecords
{
    use HasConfirmableStatusToggles;

    protected static string $resource = CoverLetterResource::class;

    protected function getHeaderActions(): array
    {
        return $this->withConfirmableStatusToggleAction([
            CreateAction::make(),
        ]);
    }
}
