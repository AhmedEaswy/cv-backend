<div class="fi-topbar-language-switcher">
    <x-filament::dropdown placement="bottom-end">
        <x-slot name="trigger">
            <x-filament::icon-button
                icon="heroicon-o-language"
                :label="__('change_language')"
                color="gray"
            />
        </x-slot>

        <x-filament::dropdown.list>
            @php
                $adminLocales = [
                    'en' => 'English',
                    'ar' => 'Arabic',
                    'tr' => 'Turkish',
                    'es' => 'Spanish',
                    'fr' => 'French',
                    'de' => 'German',
                    'ur' => 'Urdu',
                ];
            @endphp
            @foreach($adminLocales as $code => $name)
                <x-filament::dropdown.list.item
                    :href="url('/admin/switch-language/' . $code)"
                    :icon="app()->getLocale() === $code ? 'heroicon-o-check' : null"
                >
                    {{ __($name) }}
                </x-filament::dropdown.list.item>
            @endforeach
        </x-filament::dropdown.list>
    </x-filament::dropdown>
</div>
