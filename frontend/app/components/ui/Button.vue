<script setup lang="ts">
/**
 * <Button variant="primary" size="md" :loading="isSubmitting" />
 * Renders as <a> when `to` is provided, otherwise as <button>.
 */
const props = withDefaults(defineProps<{
    variant?: 'primary' | 'secondary' | 'ghost' | 'danger' | 'link';
    size?: 'sm' | 'md' | 'lg';
    type?: 'button' | 'submit' | 'reset';
    to?: string;
    href?: string;
    loading?: boolean;
    disabled?: boolean;
    block?: boolean;
    icon?: boolean;
    target?: string;
    rel?: string;
}>(), {
    variant: 'primary',
    size: 'md',
    type: 'button',
});

const sizeClass = computed(() => (props.size === 'sm' ? 'btn--sm' : props.size === 'lg' ? 'btn--lg' : ''));
const variantClass = computed(() => {
    const v = props.variant;
    return v === 'primary' ? 'btn--primary'
        : v === 'secondary' ? 'btn--secondary'
            : v === 'ghost' ? 'btn--ghost'
                : v === 'danger' ? 'btn--danger'
                    : v === 'link' ? 'btn--link' : '';
});

const btnClass = computed(() => [
    'btn',
    variantClass.value,
    sizeClass.value,
    props.block && 'btn--block',
    props.icon && 'btn--icon',
]);

const spotEnabled = computed(() => !props.disabled && !props.loading);

function onPointerMove(e: PointerEvent) {
    if (!spotEnabled.value) return;
    const el = e.currentTarget as HTMLElement;
    const rect = el.getBoundingClientRect();
    el.style.setProperty('--btn-spot-x', `${((e.clientX - rect.left) / rect.width) * 100}%`);
    el.style.setProperty('--btn-spot-y', `${((e.clientY - rect.top) / rect.height) * 100}%`);
}

function onPointerLeave(e: PointerEvent) {
    const el = e.currentTarget as HTMLElement;
    el.style.removeProperty('--btn-spot-x');
    el.style.removeProperty('--btn-spot-y');
}
</script>

<template>
    <NuxtLink
        v-if="to"
        :to="to"
        :class="btnClass"
        @pointermove="onPointerMove"
        @pointerleave="onPointerLeave"
    >
        <span class="btn__content"><slot /></span>
    </NuxtLink>
    <a
        v-else-if="href"
        :href="href"
        :target="target"
        :rel="rel"
        :class="btnClass"
        @pointermove="onPointerMove"
        @pointerleave="onPointerLeave"
    >
        <span class="btn__content"><slot /></span>
    </a>
    <button
        v-else
        :type="type"
        :disabled="disabled || loading"
        :aria-busy="loading || undefined"
        :class="btnClass"
        @pointermove="onPointerMove"
        @pointerleave="onPointerLeave"
    >
        <span class="btn__content">
            <span v-if="loading" class="btn-spinner" aria-hidden="true" />
            <slot />
        </span>
    </button>
</template>
