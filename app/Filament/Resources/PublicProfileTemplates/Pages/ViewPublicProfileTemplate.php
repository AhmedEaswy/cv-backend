<?php

namespace App\Filament\Resources\PublicProfileTemplates\Pages;

use App\Filament\Resources\PublicProfileTemplates\PublicProfileTemplateResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPublicProfileTemplate extends ViewRecord
{
    protected static string $resource = PublicProfileTemplateResource::class;

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
