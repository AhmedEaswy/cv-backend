<script setup lang="ts">
/**
 * International phone field: country dial-code picker + national number.
 * v-model stores a single string like "+966501234567".
 */
import {
    countryFlagUrl,
    defaultPhoneIso,
    findCountry,
    formatPhoneValue,
    parsePhoneValue,
    PHONE_COUNTRIES,
} from '~/utils/phoneCountries';

const model = defineModel<string | null | undefined>({ default: '' });

const props = defineProps<{
    id?: string;
    placeholder?: string;
    maxlength?: number;
    disabled?: boolean;
}>();

const { t, locale } = useI18n();
const open = ref(false);
const query = ref('');
const root = ref<HTMLElement | null>(null);
const searchRef = ref<HTMLInputElement | null>(null);

const fallbackIso = computed(() => defaultPhoneIso(locale.value));
const parsed = computed(() => parsePhoneValue(model.value, fallbackIso.value));
const iso2 = ref(parsed.value.iso2);
const national = ref(parsed.value.national);

const selected = computed(() => findCountry(iso2.value) || findCountry(fallbackIso.value)!);

const displayNames = computed(() => {
    try {
        return new Intl.DisplayNames([locale.value, 'en'], { type: 'region' });
    } catch {
        return null;
    }
});

function countryName(code: string): string {
    return displayNames.value?.of(code) || code;
}

const filteredCountries = computed(() => {
    const q = query.value.trim().toLowerCase();
    if (!q) return PHONE_COUNTRIES;
    return PHONE_COUNTRIES.filter((country) => {
        const name = countryName(country.iso2).toLowerCase();
        return (
            name.includes(q)
            || country.iso2.toLowerCase().includes(q)
            || country.dial.includes(q.replace(/^\+/, ''))
        );
    });
});

let syncingFromModel = false;

function commit() {
    if (syncingFromModel) return;
    model.value = formatPhoneValue(iso2.value, national.value);
}

watch(() => model.value, (value) => {
    const next = parsePhoneValue(value, fallbackIso.value);
    if (next.iso2 === iso2.value && next.national === national.value.replace(/\D/g, '')) return;
    syncingFromModel = true;
    iso2.value = next.iso2;
    national.value = next.national;
    nextTick(() => { syncingFromModel = false; });
});

watch([iso2, national], commit);

async function toggleOpen() {
    if (props.disabled) return;
    open.value = !open.value;
    if (open.value) {
        query.value = '';
        await nextTick();
        searchRef.value?.focus();
    }
}

function pickCountry(code: string) {
    iso2.value = code;
    open.value = false;
    query.value = '';
}

function onDocPointer(event: MouseEvent | TouchEvent) {
    if (!open.value || !root.value) return;
    const target = event.target as Node | null;
    if (target && !root.value.contains(target)) {
        open.value = false;
    }
}

function onKeydown(event: KeyboardEvent) {
    if (event.key === 'Escape') open.value = false;
}

onMounted(() => {
    document.addEventListener('mousedown', onDocPointer);
    document.addEventListener('keydown', onKeydown);
});

onBeforeUnmount(() => {
    document.removeEventListener('mousedown', onDocPointer);
    document.removeEventListener('keydown', onKeydown);
});
</script>

<template>
    <div ref="root" class="ui-phone" :class="{ 'is-open': open }">
        <button
            type="button"
            class="ui-phone__country"
            :disabled="disabled"
            :aria-label="t('ui.phone.country')"
            :aria-expanded="open"
            aria-haspopup="listbox"
            @click="toggleOpen"
        >
            <img
                class="ui-phone__flag"
                :src="countryFlagUrl(selected.iso2)"
                alt=""
                width="20"
                height="15"
                loading="lazy"
                decoding="async"
            >
            <span class="ui-phone__dial" dir="ltr">+{{ selected.dial }}</span>
            <Icon name="chevron-down" :size="14" class="ui-phone__caret" />
        </button>

        <input
            :id="id"
            v-model="national"
            type="tel"
            class="input ui-phone__input"
            dir="ltr"
            autocomplete="tel-national"
            inputmode="tel"
            :placeholder="placeholder || t('ui.phone.placeholder')"
            :maxlength="maxlength ?? 20"
            :disabled="disabled"
            :aria-label="t('ui.phone.number')"
        >

        <div
            v-if="open"
            class="ui-phone__menu"
            role="listbox"
            :aria-label="t('ui.phone.country')"
        >
            <div class="ui-phone__search">
                <input
                    ref="searchRef"
                    v-model="query"
                    type="search"
                    class="input"
                    :placeholder="t('ui.phone.search')"
                    autocomplete="off"
                >
            </div>
            <ul class="ui-phone__list">
                <li v-for="country in filteredCountries" :key="country.iso2">
                    <button
                        type="button"
                        class="ui-phone__option"
                        :class="{ 'is-active': country.iso2 === selected.iso2 }"
                        role="option"
                        :aria-selected="country.iso2 === selected.iso2"
                        @click="pickCountry(country.iso2)"
                    >
                        <img
                            class="ui-phone__flag"
                            :src="countryFlagUrl(country.iso2)"
                            alt=""
                            width="20"
                            height="15"
                            loading="lazy"
                            decoding="async"
                        >
                        <span class="ui-phone__name">{{ countryName(country.iso2) }}</span>
                        <span class="ui-phone__option-dial" dir="ltr">+{{ country.dial }}</span>
                    </button>
                </li>
                <li v-if="filteredCountries.length === 0" class="ui-phone__empty">
                    {{ t('ui.phone.no_results') }}
                </li>
            </ul>
        </div>
    </div>
</template>
