<script setup lang="ts">
/**
 * <Switch v-model="on" /> — shadcn-style toggle (role=switch).
 */
import { useId } from 'vue';
const model = defineModel<boolean | number | string | null>({ default: false });

const props = withDefaults(defineProps<{
    id?: string;
    name?: string;
    disabled?: boolean;
    size?: 'sm' | 'md';
}>(), {
    size: 'md',
});

const uid = useId();
const switchId = computed(() => props.id || `switch-${uid}`);

const isOn = computed(() => model.value === true || model.value === 1 || model.value === '1');

function toggle() {
    if (props.disabled) return;
    model.value = !isOn.value;
}
</script>

<template>
    <div class="ui-switch-field" :class="{ 'ui-switch-field--sm': size === 'sm' }">
        <button
            :id="switchId"
            type="button"
            role="switch"
            class="ui-switch"
            :class="{ 'ui-switch--sm': size === 'sm' }"
            :aria-checked="isOn"
            :disabled="disabled"
            :data-state="isOn ? 'checked' : 'unchecked'"
            @click="toggle"
        >
            <span class="ui-switch__thumb" />
        </button>
        <input
            v-if="name"
            type="hidden"
            :name="name"
            :value="isOn ? '1' : '0'"
        />
        <label v-if="$slots.default" class="ui-switch-field__copy" :for="switchId">
            <slot />
        </label>
    </div>
</template>
