<?php

namespace App\Filament\Resources\CoverLetters\Pages;

use App\Filament\Resources\CoverLetters\CoverLetterResource;
use App\Services\TrackingService;
use Filament\Resources\Pages\CreateRecord;

class CreateCoverLetter extends CreateRecord
{
    protected static string $resource = CoverLetterResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return array_merge(
            $data,
            app(TrackingService::class)->capture(request())
        );
    }
}
