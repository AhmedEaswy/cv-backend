<script setup lang="ts">
/**
 * Tom Select single-select, styled to match .input radius.
 */
import TomSelect from 'tom-select';

export interface SelectOption {
    value: string | number;
    label: string;
}

const model = defineModel<string | number | null | undefined>({ default: '' });

const props = defineProps<{
    id?: string;
    options: SelectOption[];
    placeholder?: string;
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
    <select :id="id" ref="selectEl" class="ts-select">
        <option v-if="placeholder !== undefined" value="">{{ placeholder }}</option>
        <option v-for="opt in options" :key="String(opt.value)" :value="opt.value">{{ opt.label }}</option>
    </select>
</template>
