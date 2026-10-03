<?php

namespace App\Filament\Resources\ProductTours\Pages;

use App\Filament\Resources\ProductTours\ProductTourResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProductTours extends ListRecords
{
    protected static string $resource = ProductTourResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
