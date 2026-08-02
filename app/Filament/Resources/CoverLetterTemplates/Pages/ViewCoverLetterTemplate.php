<?php

namespace App\Filament\Resources\CoverLetterTemplates\Pages;

use App\Filament\Resources\CoverLetterTemplates\CoverLetterTemplateResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCoverLetterTemplate extends ViewRecord
{
    protected static string $resource = CoverLetterTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    public function getContentTabLabel(): ?string
    {
        return __('Template info');
    }
}
