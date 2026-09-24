<x-filament-panels::page>
    @include('filament.pages.reports.partials.date-range')

    <div class="mb-4 max-w-xs">
        <label class="mb-1 block text-sm font-medium text-gray-950 dark:text-white">{{ __('App platform') }}</label>
        <select
            wire:model.live="platform"
            class="fi-select-input block w-full rounded-lg border-none bg-white px-3 py-1.5 text-sm shadow-sm ring-1 ring-gray-950/10 dark:bg-white/5 dark:text-white dark:ring-white/20"
        >
            <option value="">{{ __('All platforms') }}</option>
            <option value="ios">iOS</option>
            <option value="android">Android</option>
            <option value="web">Web</option>
            <option value="api">API</option>
        </select>
    </div>

    @php($totals = $this->getTotals())

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
        @foreach ($totals as $action => $count)
            <x-filament::section>
                <div class="text-sm text-gray-500 dark:text-gray-400">{{ str_replace('_', ' ', $action) }}</div>
                <div class="mt-1 text-2xl font-semibold text-gray-950 dark:text-white">{{ number_format($count) }}</div>
            </x-filament::section>
        @endforeach
    </div>

    <x-filament::section>
        <x-slot name="heading">{{ __('Daily breakdown') }}</x-slot>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-white/10">
                        <th class="px-3 py-2">{{ __('Day') }}</th>
                        <th class="px-3 py-2">register</th>
                        <th class="px-3 py-2">create_cv</th>
                        <th class="px-3 py-2">print_cv</th>
                        <th class="px-3 py-2">create_cover_letter</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->getDailyRows() as $row)
                        <tr class="border-b border-gray-100 dark:border-white/5">
                            <td class="px-3 py-2">{{ $row['day'] }}</td>
                            <td class="px-3 py-2">{{ $row['register'] }}</td>
                            <td class="px-3 py-2">{{ $row['create_cv'] }}</td>
                            <td class="px-3 py-2">{{ $row['print_cv'] }}</td>
                            <td class="px-3 py-2">{{ $row['create_cover_letter'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-3 py-6 text-gray-500">{{ __('No events in this range.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-panels::page>
