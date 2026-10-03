<script setup lang="ts">
/**
 * Single-select field. Same radius and focus ring as .input, with an optional leading icon.
 */
import TomSelect from 'tom-select';
import { HugeiconsIcon } from '@hugeicons/vue';
import { ChevronDownIcon } from '@hugeicons/core-free-icons';

export interface SelectOption {
    value: string | number;
    label: string;
}

export type SelectIcon = typeof ChevronDownIcon;

const model = defineModel<string | number | null | undefined>({ default: '' });

const props = defineProps<{
    id?: string;
    options: SelectOption[];
    placeholder?: string;
    icon?: SelectIcon;
}>();

const selectEl = ref<HTMLSelectElement | null>(null);
let ts: TomSelect | null = null;
let syncing = false;

function coerce(raw: string): string | number {
    if (raw === '') return '';
    const match = props.options.find((o) => String(o.value) === raw);
    return match ? match.value : raw;
}

function bind() {
    if (!selectEl.value) return;
    ts?.destroy();
    ts = new TomSelect(selectEl.value, {
        allowEmptyOption: true,
        maxItems: 1,
        hideSelected: false,
        dropdownParent: 'body',
        placeholder: props.placeholder || undefined,
        onChange(value: string | string[]) {
            if (syncing) return;
            const raw = Array.isArray(value) ? (value[0] ?? '') : value;
            model.value = coerce(raw);
        },
    });
    syncing = true;
    ts.setValue(model.value == null ? '' : String(model.value), true);
    syncing = false;
}

onMounted(() => {
    bind();
});

watch(() => props.options, () => {
    nextTick(() => bind());
}, { deep: true });

watch(() => model.value, (value) => {
    if (!ts) return;
    const next = value == null ? '' : String(value);
    if (String(ts.getValue()) !== next) {
        syncing = true;
        ts.setValue(next, true);
        syncing = false;
    }
});

onBeforeUnmount(() => {
    ts?.destroy();
    ts = null;
});
</script>

<template>
    <div class="ui-select" :class="{ 'ui-select--icon': !!icon }">
        <span v-if="icon" class="ui-select__icon" aria-hidden="true">
            <HugeiconsIcon :icon="icon" :size="16" :stroke-width="1.75" />
        </span>
        <select :id="id" ref="selectEl" class="ts-select">
            <option v-if="placeholder !== undefined" value="">{{ placeholder }}</option>
            <option v-for="opt in options" :key="String(opt.value)" :value="opt.value">{{ opt.label }}</option>
        </select>
        <span class="ui-select__caret" aria-hidden="true">
            <HugeiconsIcon :icon="ChevronDownIcon" :size="16" :stroke-width="1.75" />
        </span>
    </div>
</template>
