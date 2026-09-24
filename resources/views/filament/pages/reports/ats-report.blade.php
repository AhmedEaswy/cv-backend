<x-filament-panels::page>
    @include('filament.pages.reports.partials.date-range')

    @php($summary = $this->getSummary())

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-filament::section>
            <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Checks') }}</div>
            <div class="mt-1 text-3xl font-semibold text-gray-950 dark:text-white">{{ number_format($summary['total']) }}</div>
        </x-filament::section>
        <x-filament::section>
            <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Avg score') }}</div>
            <div class="mt-1 text-3xl font-semibold text-gray-950 dark:text-white">{{ $summary['avg_score'] }}%</div>
        </x-filament::section>
        <x-filament::section>
            <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Pass rate') }}</div>
            <div class="mt-1 text-3xl font-semibold text-gray-950 dark:text-white">{{ $summary['pass_rate'] }}%</div>
        </x-filament::section>
        <x-filament::section>
            <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('With job description') }}</div>
            <div class="mt-1 text-3xl font-semibold text-gray-950 dark:text-white">{{ number_format($summary['with_jd']) }}</div>
        </x-filament::section>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <x-filament::section>
            <x-slot name="heading">{{ __('By language') }}</x-slot>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-white/10">
                        <th class="px-3 py-2 text-left">{{ __('Language') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('Checks') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('Avg score') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($summary['by_language'] as $row)
                        <tr class="border-b border-gray-100 dark:border-white/5">
                            <td class="px-3 py-2">{{ $row->language ?? '—' }}</td>
                            <td class="px-3 py-2">{{ $row->total }}</td>
                            <td class="px-3 py-2">{{ (int) round((float) $row->avg_score) }}%</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-3 py-6 text-gray-500">{{ __('No data.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">{{ __('By grade') }}</x-slot>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-white/10">
                        <th class="px-3 py-2 text-left">{{ __('Grade') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('Checks') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($summary['by_grade'] as $row)
                        <tr class="border-b border-gray-100 dark:border-white/5">
                            <td class="px-3 py-2">{{ $row->grade ?? '—' }}</td>
                            <td class="px-3 py-2">{{ $row->total }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="px-3 py-6 text-gray-500">{{ __('No data.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </x-filament::section>
    </div>
</x-filament-panels::page>
