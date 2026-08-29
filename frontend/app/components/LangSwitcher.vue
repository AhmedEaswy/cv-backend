<script setup lang="ts">
/**
 * <LangSwitcher />
 *
 * Language dropdown. Menu is teleported + fixed under the trigger so the
 * fixed/blurred header cannot skew absolute positioning. Locale change
 * uses setLocale(); messages come from the i18n-messages plugin.
 */
const { locale, locales, t, setLocale } = useI18n();

const MENU_WIDTH = 176;
const MENU_GAP = 6;
const VIEW_PAD = 8;

const open = ref(false);
const rootEl = ref<HTMLElement | null>(null);
const triggerEl = ref<HTMLButtonElement | null>(null);
const menuEl = ref<HTMLElement | null>(null);

const menuStyle = ref<Record<string, string>>({});

const list = computed(() =>
    (locales.value as Array<{ code: string; name: string; dir?: 'ltr' | 'rtl' }>) || [],
);
const current = computed(() => list.value.find((l) => l.code === locale.value));

const codeLabel = computed(() => {
    if (!current.value) return String(locale.value).toUpperCase();
    const map: Record<string, string> = { ar: 'ع', ur: 'UR' };
    return map[current.value.code] ?? current.value.code.toUpperCase();
});

const placeMenu = () => {
    const trigger = triggerEl.value;
    if (!trigger || !import.meta.client) return;

    const rect = trigger.getBoundingClientRect();
    const menuWidth = MENU_WIDTH;
    const menuHeight = menuEl.value?.offsetHeight || 260;

    // Prefer aligning under the trigger's left edge; flip if it would overflow.
    let left = rect.left;
    if (left + menuWidth > window.innerWidth - VIEW_PAD) {
        left = rect.right - menuWidth;
    }
    left = Math.min(Math.max(VIEW_PAD, left), window.innerWidth - menuWidth - VIEW_PAD);

    let top = rect.bottom + MENU_GAP;
    if (top + menuHeight > window.innerHeight - VIEW_PAD) {
        top = Math.max(VIEW_PAD, rect.top - menuHeight - MENU_GAP);
    }

    menuStyle.value = {
        position: 'fixed',
        top: `${Math.round(top)}px`,
        left: `${Math.round(left)}px`,
        zIndex: '1000',
    };
};

const close = () => { open.value = false; };

const toggle = async () => {
    if (open.value) {
        close();
        return;
    }
    const rect = triggerEl.value?.getBoundingClientRect();
    if (rect) {
        menuStyle.value = {
            position: 'fixed',
            top: `${Math.round(rect.bottom + MENU_GAP)}px`,
            left: `${Math.round(rect.left)}px`,
            zIndex: '1000',
        };
    }
    open.value = true;
    await nextTick();
    requestAnimationFrame(() => placeMenu());
};

const onDocClick = (e: MouseEvent) => {
    const target = e.target as Node;
    if (rootEl.value?.contains(target)) return;
    if (menuEl.value?.contains(target)) return;
    close();
};
const onKey = (e: KeyboardEvent) => { if (e.key === 'Escape') close(); };
const onReposition = () => { if (open.value) placeMenu(); };

onMounted(() => {
    document.addEventListener('click', onDocClick);
    document.addEventListener('keydown', onKey);
    window.addEventListener('resize', onReposition);
    window.addEventListener('scroll', onReposition, true);
});
onBeforeUnmount(() => {
    document.removeEventListener('click', onDocClick);
    document.removeEventListener('keydown', onKey);
    window.removeEventListener('resize', onReposition);
    window.removeEventListener('scroll', onReposition, true);
});

const onPick = async (code: string) => {
    close();
    if (code === locale.value) return;
    await setLocale(code);
};
</script>

<template>
    <div ref="rootEl" class="lang-dropdown" data-lang-dropdown>
        <button
            ref="triggerEl"
            type="button"
            class="lang-trigger"
            aria-haspopup="listbox"
            :aria-expanded="open"
            :aria-label="t('change_language')"
            data-lang-trigger
            @click.stop="toggle"
        >
            <span>{{ codeLabel }}</span>
            <svg class="chev" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="m6 9 6 6 6-6" />
            </svg>
        </button>

        <Teleport to="body">
            <div
                v-show="open"
                ref="menuEl"
                class="lang-menu"
                role="listbox"
                data-lang-menu
                :style="menuStyle"
            >
                <button
                    v-for="opt in list"
                    :key="opt.code"
                    type="button"
                    class="lang-option"
                    role="option"
                    :lang="opt.code"
                    :aria-selected="opt.code === locale"
                    @click="onPick(opt.code)"
                >
                    <span>{{ opt.name }}</span>
                </button>
            </div>
        </Teleport>
    </div>
</template>
