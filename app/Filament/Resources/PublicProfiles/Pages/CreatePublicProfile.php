<?php

namespace App\Filament\Resources\PublicProfiles\Pages;

use App\Filament\Resources\PublicProfiles\PublicProfileResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePublicProfile extends CreateRecord
{
    protected static string $resource = PublicProfileResource::class;
}
