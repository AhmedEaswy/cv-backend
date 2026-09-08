<script setup lang="ts">
/**
 * Flatpickr date / month input styled like .input
 */
import flatpickr from 'flatpickr';
import type { Instance } from 'flatpickr/dist/types/instance';

const model = defineModel<string | null | undefined>({ default: '' });

const props = withDefaults(defineProps<{
    id?: string;
    mode?: 'date' | 'month';
    placeholder?: string;
}>(), {
    mode: 'date',
});

const input = ref<HTMLInputElement | null>(null);
let fp: Instance | null = null;

function dateFormat() {
    return props.mode === 'month' ? 'Y-m' : 'Y-m-d';
}

onMounted(() => {
    if (!input.value) return;
    fp = flatpickr(input.value, {
        dateFormat: dateFormat(),
        allowInput: true,
        disableMobile: true,
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

onBeforeUnmount(() => {
    fp?.destroy();
    fp = null;
});
</script>

<template>
    <input
        :id="id"
        ref="input"
        type="text"
        class="input"
        :placeholder="placeholder || (mode === 'month' ? 'YYYY-MM' : 'YYYY-MM-DD')"
        autocomplete="off"
        readonly
    />
</template>
