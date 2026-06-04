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
            <x-filament::dropdown.list.item
                :href="url('/admin/switch-language/en')"
                :icon="app()->getLocale() === 'en' ? 'heroicon-o-check' : null"
            >
                {{ __('English') }}
            </x-filament::dropdown.list.item>

            <x-filament::dropdown.list.item
                :href="url('/admin/switch-language/ar')"
                :icon="app()->getLocale() === 'ar' ? 'heroicon-o-check' : null"
            >
                {{ __('Arabic') }}
            </x-filament::dropdown.list.item>
        </x-filament::dropdown.list>
    </x-filament::dropdown>
</div>
