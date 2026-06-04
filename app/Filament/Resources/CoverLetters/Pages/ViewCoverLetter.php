<?php

namespace App\Filament\Resources\CoverLetters\Pages;

use App\Filament\Resources\CoverLetters\CoverLetterResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCoverLetter extends ViewRecord
{
    protected static string $resource = CoverLetterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
