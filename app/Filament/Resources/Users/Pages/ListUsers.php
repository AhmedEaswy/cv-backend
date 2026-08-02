<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Concerns\HasConfirmableStatusToggles;
use App\Filament\Resources\Users\UserResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    use HasConfirmableStatusToggles;

    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return $this->withConfirmableStatusToggleAction([
            CreateAction::make(),
        ]);
    }
}
