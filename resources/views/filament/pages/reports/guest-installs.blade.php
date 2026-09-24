<x-filament-panels::page>
    @include('filament.pages.reports.partials.date-range')

    <x-filament::section class="mb-6">
        <x-slot name="heading">{{ __('New installs by day') }}</x-slot>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-white/10">
                        <th class="px-3 py-2 text-left">{{ __('Day') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('Installs') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->getByDay() as $row)
                        <tr class="border-b border-gray-100 dark:border-white/5">
                            <td class="px-3 py-2">{{ $row->day }}</td>
                            <td class="px-3 py-2">{{ $row->total }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="px-3 py-6 text-gray-500">{{ __('No installs in this range.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>

    <x-filament::section>
        <x-slot name="heading">{{ __('Recent installs') }}</x-slot>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-white/10">
                        <th class="px-3 py-2 text-left">{{ __('Install ID') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('First seen') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('Last seen') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('Platform') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('OS') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('Model') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('Country') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('CVs') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('Letters') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->getInstalls() as $install)
                        <tr class="border-b border-gray-100 dark:border-white/5">
                            <td class="px-3 py-2 font-mono text-xs">{{ \Illuminate\Support\Str::limit($install->id, 13, '…') }}</td>
                            <td class="px-3 py-2">{{ $install->first_seen_at?->toDateTimeString() }}</td>
                            <td class="px-3 py-2">{{ $install->last_seen_at?->toDateTimeString() }}</td>
                            <td class="px-3 py-2">{{ $install->app_platform ?? '—' }}</td>
                            <td class="px-3 py-2">{{ trim(($install->os ?? '').' '.($install->os_version ?? '')) ?: '—' }}</td>
                            <td class="px-3 py-2">{{ $install->device_model ?? '—' }}</td>
                            <td class="px-3 py-2">{{ $install->country ?? '—' }}</td>
                            <td class="px-3 py-2">{{ $install->profiles_count }}</td>
                            <td class="px-3 py-2">{{ $install->cover_letters_count }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-3 py-6 text-gray-500">{{ __('No installs in this range.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-panels::page>
