<?php

namespace App\Filament\Resources\ProductTours\Pages;

use App\Filament\Resources\ProductTours\ProductTourResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProductTour extends EditRecord
{
    protected static string $resource = ProductTourResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
