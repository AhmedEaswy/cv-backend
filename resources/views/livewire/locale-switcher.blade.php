<div class="flex items-center gap-2">
    <div class="flex bg-gray-100 dark:bg-gray-800 rounded-lg p-1">
        <button
            wire:click="switchLocale('en')"
            class="px-2 py-1 text-xs rounded-md transition-colors duration-200 {{ $this->getCurrentLocale() === 'en' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}"
            title="English"
        >
            🇺🇸 EN
        </button>
        <button
            wire:click="switchLocale('ar')"
            class="px-2 py-1 text-xs rounded-md transition-colors duration-200 {{ $this->getCurrentLocale() === 'ar' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}"
            title="العربية"
        >
            🇸🇦 ع
        </button>
    </div>
</div>
