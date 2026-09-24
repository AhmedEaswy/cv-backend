<x-filament-panels::page>
    @include('filament.pages.reports.partials.date-range')

    <div class="mb-4 max-w-xs">
        <label class="mb-1 block text-sm font-medium text-gray-950 dark:text-white">{{ __('Group by') }}</label>
        <select
            wire:model.live="groupBy"
            class="fi-select-input block w-full rounded-lg border-none bg-white px-3 py-1.5 text-sm shadow-sm ring-1 ring-gray-950/10 dark:bg-white/5 dark:text-white dark:ring-white/20"
        >
            <option value="os">{{ __('OS') }}</option>
            <option value="os_version">{{ __('OS version') }}</option>
            <option value="device_model">{{ __('Device model') }}</option>
            <option value="app_platform">{{ __('App platform') }}</option>
            <option value="device_type">{{ __('Device type') }}</option>
        </select>
    </div>

    <x-filament::section>
        <x-slot name="heading">{{ __('Breakdown') }}</x-slot>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-white/10">
                        <th class="px-3 py-2 text-left">{{ __('Label') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('Events') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->getBreakdown() as $row)
                        <tr class="border-b border-gray-100 dark:border-white/5">
                            <td class="px-3 py-2">{{ $row->label }}</td>
                            <td class="px-3 py-2">{{ number_format($row->total) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="px-3 py-6 text-gray-500">{{ __('No device data in this range.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-panels::page>
