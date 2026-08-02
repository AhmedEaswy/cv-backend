<?php

namespace App\Filament\Support;

use Filament\Tables\Columns\ToggleColumn;
use Illuminate\Database\Eloquent\Model;

class ConfirmableToggle
{
    /**
     * @param  class-string  $resourceClass
     */
    public static function make(string $name, string $resourceClass, ?string $label = null): ToggleColumn
    {
        $column = ToggleColumn::make($name)
            ->disabled(fn (?Model $record): bool => ! $record || ! $resourceClass::canEdit($record))
            ->updateStateUsing(function (mixed $state, Model $record, $livewire) use ($name): void {
                $livewire->mountAction('confirmStatusToggle', [
                    'recordKey' => $record->getKey(),
                    'column' => $name,
                    'state' => (bool) $state,
                ]);
            });

        if (filled($label)) {
            $column->label($label);
        }

        return $column;
    }
}
