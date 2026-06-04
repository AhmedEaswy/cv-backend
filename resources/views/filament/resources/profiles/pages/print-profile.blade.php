<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">
            {{ __('Print Profile') }}
        </x-slot>

        <x-slot name="description">
            {{ __('Select a template and generate a PDF for this profile.') }}
        </x-slot>

        <form wire:submit="generatePdf">
            {{ $this->form }}

            <div class="mt-6 flex justify-end gap-3">
                <x-filament::button type="button" color="gray" wire:click="cancel">
                    {{ __('Cancel') }}
                </x-filament::button>
                <x-filament::button type="submit" color="success" icon="heroicon-o-printer">
                    {{ __('Generate PDF') }}
                </x-filament::button>
            </div>
        </form>
    </x-filament::section>
</x-filament-panels::page>
