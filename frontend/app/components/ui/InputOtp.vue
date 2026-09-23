<script setup lang="ts">
/**
 * Shadcn-style OTP input (6 digit slots + invisible input).
 * Paste, arrow keys, backspace, and one-time-code autocomplete supported.
 */
const props = withDefaults(defineProps<{
    id?: string;
    modelValue?: string;
    length?: number;
    disabled?: boolean;
    invalid?: boolean;
    autofocus?: boolean;
    autocomplete?: string;
    name?: string;
}>(), {
    modelValue: '',
    length: 6,
    autocomplete: 'one-time-code',
});

const emit = defineEmits<{
    'update:modelValue': [value: string];
    complete: [value: string];
}>();

const inputRef = ref<HTMLInputElement | null>(null);
const isFocused = ref(false);

const digits = computed(() => {
    const cleaned = String(props.modelValue ?? '').replace(/\D/g, '').slice(0, props.length);
    return Array.from({ length: props.length }, (_, i) => cleaned[i] ?? '');
});

const activeIndex = computed(() => {
    const filled = digits.value.findIndex(d => d === '');
    return filled === -1 ? props.length - 1 : filled;
});

function normalize(raw: string): string {
    return raw.replace(/\D/g, '').slice(0, props.length);
}

function setValue(next: string) {
    const value = normalize(next);
    emit('update:modelValue', value);
    if (value.length === props.length) {
        emit('complete', value);
    }
}

function onInput(event: Event) {
    const target = event.target as HTMLInputElement;
    setValue(target.value);
    // Keep the invisible input value in sync with normalized digits.
    target.value = normalize(target.value);
}

function onKeydown(event: KeyboardEvent) {
    if (event.key !== 'Backspace') return;
    const current = normalize(props.modelValue);
    if (current.length === 0) return;
    // Allow natural backspace when caret is at end; ensure we always shrink by one.
    if ((event.target as HTMLInputElement).selectionStart === current.length) {
        event.preventDefault();
        setValue(current.slice(0, -1));
    }
}

function onPaste(event: ClipboardEvent) {
    event.preventDefault();
    const text = event.clipboardData?.getData('text') ?? '';
    setValue(text);
}

function focusInput() {
    inputRef.value?.focus();
}

function onContainerClick() {
    if (!props.disabled) focusInput();
}

onMounted(() => {
    if (props.autofocus) focusInput();
});

defineExpose({ focus: focusInput });
</script>

<template>
    <div
        class="otp-input"
        :class="{
            'otp-input--invalid': invalid,
            'otp-input--disabled': disabled,
            'otp-input--focused': isFocused,
        }"
        role="group"
        dir="ltr"
        @click="onContainerClick"
    >
        <input
            :id="id"
            ref="inputRef"
            class="otp-input__control"
            type="text"
            inputmode="numeric"
            pattern="[0-9]*"
            :name="name"
            :disabled="disabled"
            :autocomplete="autocomplete"
            :maxlength="length"
            :value="modelValue"
            :aria-invalid="invalid || undefined"
            :aria-label="id ? undefined : 'One-time password'"
            @input="onInput"
            @keydown="onKeydown"
            @paste="onPaste"
            @focus="isFocused = true"
            @blur="isFocused = false"
        >

        <div class="otp-input__slots" aria-hidden="true">
            <div
                v-for="(digit, index) in digits"
                :key="index"
                class="otp-input__slot"
                :class="{
                    'otp-input__slot--active': isFocused && index === activeIndex,
                    'otp-input__slot--filled': digit !== '',
                }"
            >
                <span v-if="digit" class="otp-input__char">{{ digit }}</span>
                <span
                    v-else-if="isFocused && index === activeIndex"
                    class="otp-input__caret"
                />
            </div>
        </div>
    </div>
</template>
