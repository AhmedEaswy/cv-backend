<script setup lang="ts">
/**
 * Flatpickr date / month input styled like .input.
 * Calendar UI follows the active app locale (en/ar/tr/es/fr/de; ur falls back to en).
 */
import flatpickr from 'flatpickr';
import type { Instance } from 'flatpickr/dist/types/instance';
import type { CustomLocale } from 'flatpickr/dist/types/locale';
import { Arabic } from 'flatpickr/dist/l10n/ar.js';
import { German } from 'flatpickr/dist/l10n/de.js';
import { Spanish } from 'flatpickr/dist/l10n/es.js';
import { French } from 'flatpickr/dist/l10n/fr.js';
import { Turkish } from 'flatpickr/dist/l10n/tr.js';
import { HugeiconsIcon } from '@hugeicons/vue';
import { Calendar03Icon } from '@hugeicons/core-free-icons';

const model = defineModel<string | null | undefined>({ default: '' });

const props = withDefaults(defineProps<{
    id?: string;
    mode?: 'date' | 'month';
    placeholder?: string;
}>(), {
    mode: 'date',
});

const { t, locale } = useI18n();
const input = ref<HTMLInputElement | null>(null);
let fp: Instance | null = null;

const FLATPICKR_LOCALES: Record<string, CustomLocale> = {
    ar: Arabic,
    de: German,
    es: Spanish,
    fr: French,
    tr: Turkish,
};

function dateFormat() {
    return props.mode === 'month' ? 'Y-m' : 'Y-m-d';
}

function flatpickrLocale(): CustomLocale | 'default' {
    return FLATPICKR_LOCALES[locale.value] ?? 'default';
}

const resolvedPlaceholder = computed(() => {
    if (props.placeholder) return props.placeholder;
    return props.mode === 'month'
        ? t('ui.datepicker.placeholder_month')
        : t('ui.datepicker.placeholder_date');
});

onMounted(() => {
    if (!input.value) return;
    fp = flatpickr(input.value, {
        dateFormat: dateFormat(),
        allowInput: true,
        disableMobile: true,
        locale: flatpickrLocale(),
        defaultDate: model.value || undefined,
        onChange(_dates, dateStr) {
            model.value = dateStr || '';
        },
    });
});

watch(() => model.value, (value) => {
    if (!fp) return;
    const next = value || '';
    if (fp.input.value !== next) {
        fp.setDate(next, false);
    }
});

watch(locale, () => {
    fp?.set('locale', flatpickrLocale());
});

onBeforeUnmount(() => {
    fp?.destroy();
    fp = null;
});
</script>

<template>
    <div class="ui-date">
        <span class="ui-date__icon" aria-hidden="true">
            <HugeiconsIcon :icon="Calendar03Icon" :size="16" :stroke-width="1.75" />
        </span>
        <input
            :id="id"
            ref="input"
            type="text"
            class="input"
            :placeholder="resolvedPlaceholder"
            autocomplete="off"
            readonly
            dir="ltr"
        />
    </div>
</template>
