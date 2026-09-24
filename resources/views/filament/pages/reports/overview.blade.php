<x-filament-panels::page>
    @include('filament.pages.reports.partials.date-range')

    @php($stats = $this->getStats())

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['label' => __('New users'), 'value' => $stats['users']],
            ['label' => __('Guest installs'), 'value' => $stats['guest_installs']],
            ['label' => __('Active users (last used)'), 'value' => $stats['active_users']],
            ['label' => __('Users on web + mobile'), 'value' => $stats['users_both_platforms']],
            ['label' => __('CVs created'), 'value' => $stats['cvs']],
            ['label' => __('Cover letters'), 'value' => $stats['cover_letters']],
            ['label' => __('PDF prints'), 'value' => $stats['prints']],
            ['label' => __('ATS checks'), 'value' => $stats['ats_checks']],
            ['label' => __('Store clicks'), 'value' => $stats['store_clicks']],
        ] as $card)
            <x-filament::section>
                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $card['label'] }}</div>
                <div class="mt-1 text-3xl font-semibold tracking-tight text-gray-950 dark:text-white">
                    {{ number_format($card['value']) }}
                </div>
            </x-filament::section>
        @endforeach
    </div>
</x-filament-panels::page>
