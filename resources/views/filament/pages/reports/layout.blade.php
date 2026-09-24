<div class="fi-page space-y-6">
    <x-filament::section>
        <x-slot name="heading">{{ __('Date range') }}</x-slot>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 max-w-xl">
            <div>
                <label class="fi-fo-field-wrp-label inline-flex items-center gap-x-1 text-sm font-medium text-gray-950 dark:text-white">
                    {{ __('From') }}
                </label>
                <input
                    type="date"
                    wire:model.live="from"
                    class="fi-input block w-full rounded-lg border-none bg-white px-3 py-1.5 text-sm shadow-sm ring-1 ring-gray-950/10 dark:bg-white/5 dark:ring-white/20"
                />
            </div>
            <div>
                <label class="fi-fo-field-wrp-label inline-flex items-center gap-x-1 text-sm font-medium text-gray-950 dark:text-white">
                    {{ __('Until') }}
                </label>
                <input
                    type="date"
                    wire:model.live="until"
                    class="fi-input block w-full rounded-lg border-none bg-white px-3 py-1.5 text-sm shadow-sm ring-1 ring-gray-950/10 dark:bg-white/5 dark:ring-white/20"
                />
            </div>
        </div>
    </x-filament::section>

    {{ $slot ?? '' }}
</div>
