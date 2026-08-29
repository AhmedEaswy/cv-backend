<script setup lang="ts">
/**
 * <LangSwitcher />
 *
 * Language dropdown. Replaces resources/views/landing/index.blade.php's
 * `[data-lang-dropdown]` block. Click-outside + Escape close the menu.
 *
 * On locale change we use $i18n.setLocale() which updates the i18n cookie
 * (configured by @nuxtjs/i18n), then reload so SSR re-renders with the
 * new direction. switchLocalePath() in 'no_prefix' strategy is unreliable,
 * so we drive the change via the i18n composable directly.
 */
const { locale, locales, t, setLocale } = useI18n();

const open = ref(false);
const rootEl = ref<HTMLElement | null>(null);
const triggerEl = ref<HTMLButtonElement | null>(null);

const list = computed(() => (locales.value as Array<{ code: string; name: string; dir?: 'ltr' | 'rtl' }>) || []);
const current = computed(() => list.value.find((l) => l.code === locale.value));

const codeLabel = computed(() => {
    if (!current.value) return (locale.value as string).toUpperCase();
    const map: Record<string, string> = { ar: 'ع', ur: 'UR' };
    return map[current.value.code] ?? current.value.code.toUpperCase();
});

const close = () => { open.value = false; triggerEl.value?.setAttribute('aria-expanded', 'false'); };
const toggle = () => {
    open.value = !open.value;
    triggerEl.value?.setAttribute('aria-expanded', open.value ? 'true' : 'false');
};

const onDocClick = (e: MouseEvent) => {
    if (!rootEl.value) return;
    if (!rootEl.value.contains(e.target as Node)) close();
};
const onKey = (e: KeyboardEvent) => { if (e.key === 'Escape') close(); };

onMounted(() => {
    document.addEventListener('click', onDocClick);
    document.addEventListener('keydown', onKey);
});
onBeforeUnmount(() => {
    document.removeEventListener('click', onDocClick);
    document.removeEventListener('keydown', onKey);
});

const onPick = async (code: string) => {
    close();
    if (code === locale.value) return;
    try {
        await setLocale(code);
    } catch {
        // If setLocale fails (e.g. strategy restrictions), fall back to a
        // hard reload with a query param hint so SSR re-renders.
        window.location.reload();
    }
};
</script>

<template>
    <div ref="rootEl" class="lang-dropdown" data-lang-dropdown>
        <button
            ref="triggerEl"
            type="button"
            class="lang-trigger"
            aria-haspopup="listbox"
            :aria-expanded="open ? 'true' : 'false'"
            :aria-label="t('change_language')"
            data-lang-trigger
            @click.stop="toggle"
        >
            <span>{{ codeLabel }}</span>
            <svg class="chev" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="m6 9 6 6 6-6" />
            </svg>
        </button>

        <div class="lang-menu" :class="{ open }" role="listbox" data-lang-menu>
            <a
                v-for="opt in list"
                :key="opt.code"
                href="#"
                class="lang-option"
                role="option"
                :lang="opt.code"
                :dir="opt.dir ?? 'ltr'"
                :aria-current="opt.code === locale ? 'true' : 'false'"
                @click.prevent="onPick(opt.code)"
            >
                <span>{{ opt.name }}</span>
            </a>
        </div>
    </div>
</template>
