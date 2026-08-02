<?php

namespace App\Filament\Concerns;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

trait HasConfirmableStatusToggles
{
    protected function confirmStatusToggleAction(): Action
    {
        return Action::make('confirmStatusToggle')
            ->requiresConfirmation()
            ->modalHeading(function (array $arguments): string {
                $column = $arguments['column'] ?? 'status';
                $state = (bool) ($arguments['state'] ?? false);
                $label = $this->statusToggleColumnLabel($column);

                return $state
                    ? __('Enable :label?', ['label' => $label])
                    : __('Disable :label?', ['label' => $label]);
            })
            ->modalDescription(__('This change will be saved immediately after confirmation.'))
            ->modalSubmitActionLabel(__('Confirm'))
            ->action(function (array $arguments): void {
                $record = $this->resolveStatusToggleRecord($arguments['recordKey'] ?? null);

                if (! $record || ! static::getResource()::canEdit($record)) {
                    Notification::make()
                        ->title(__('You do not have permission to update this record.'))
                        ->danger()
                        ->send();

                    $this->resetTable();

                    return;
                }

                $column = (string) ($arguments['column'] ?? '');
                $state = (bool) ($arguments['state'] ?? false);

                if ($column === '' || ! in_array($column, $this->confirmableStatusColumns(), true)) {
                    $this->resetTable();

                    return;
                }

                if ($column === 'is_default' && $state) {
                    $record->newQuery()
                        ->whereKeyNot($record->getKey())
                        ->where('is_default', true)
                        ->update(['is_default' => false]);
                }

                $record->update([$column => $state]);

                Notification::make()
                    ->title(__(':label updated', ['label' => $this->statusToggleColumnLabel($column)]))
                    ->success()
                    ->send();

                $this->resetTable();
            })
            ->modalCancelAction(fn (Action $action): Action => $action->action(function (): void {
                $this->resetTable();
            }))
            ->hidden();
    }

    /**
     * @return list<string>
     */
    protected function confirmableStatusColumns(): array
    {
        return ['active', 'is_active', 'is_default', 'is_public'];
    }

    protected function statusToggleColumnLabel(string $column): string
    {
        return match ($column) {
            'active', 'is_active' => __('Active'),
            'is_default' => __('Default'),
            'is_public' => __('Public'),
            default => __(str($column)->replace('_', ' ')->title()->toString()),
        };
    }

    protected function resolveStatusToggleRecord(mixed $recordKey): ?Model
    {
        if (blank($recordKey)) {
            return null;
        }

        return static::getResource()::getEloquentQuery()->find($recordKey);
    }

    /**
     * @param  array<int, Action|\Filament\Actions\ActionGroup>  $actions
     * @return array<int, Action|\Filament\Actions\ActionGroup>
     */
    protected function withConfirmableStatusToggleAction(array $actions = []): array
    {
        return [
            ...$actions,
            $this->confirmStatusToggleAction(),
        ];
    }
}
