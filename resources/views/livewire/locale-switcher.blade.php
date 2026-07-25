<div class="flex items-center gap-2">
    @php
        $locales = [
            'en' => ['code' => 'EN', 'name' => 'English'],
            'ar' => ['code' => 'ع',  'name' => 'العربية'],
            'tr' => ['code' => 'TR', 'name' => 'Türkçe'],
            'es' => ['code' => 'ES', 'name' => 'Español'],
            'fr' => ['code' => 'FR', 'name' => 'Français'],
            'de' => ['code' => 'DE', 'name' => 'Deutsch'],
            'ur' => ['code' => 'UR', 'name' => 'اردو'],
        ];
    @endphp
    <div class="flex bg-gray-100 dark:bg-gray-800 rounded-lg p-1">
        @foreach($locales as $code => $meta)
            <button
                wire:click="switchLocale('{{ $code }}')"
                class="px-2 py-1 text-xs rounded-md transition-colors duration-200 {{ $this->getCurrentLocale() === $code ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}"
                title="{{ $meta['name'] }}"
                lang="{{ $code }}"
                dir="{{ in_array($code, ['ar', 'ur']) ? 'rtl' : 'ltr' }}"
            >
                {{ $meta['code'] }}
            </button>
        @endforeach
    </div>
</div>
