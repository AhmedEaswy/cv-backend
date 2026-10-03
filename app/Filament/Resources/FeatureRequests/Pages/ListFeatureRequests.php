<?php

namespace App\Filament\Resources\FeatureRequests\Pages;

use App\Filament\Resources\FeatureRequests\FeatureRequestResource;
use Filament\Resources\Pages\ListRecords;

class ListFeatureRequests extends ListRecords
{
    protected static string $resource = FeatureRequestResource::class;
}
