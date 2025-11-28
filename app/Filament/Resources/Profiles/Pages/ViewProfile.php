<?php

namespace App\Filament\Resources\Profiles\Pages;

use App\Filament\Resources\Profiles\ProfileResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewProfile extends ViewRecord
{
    protected static string $resource = ProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('print')
                ->label('Print CV')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->url(fn () => ProfileResource::getUrl('print', ['record' => $this->record])),
            EditAction::make(),
        ];
    }
}
