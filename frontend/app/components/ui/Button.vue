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
</script>

<template>
    <NuxtLink
        v-if="to"
        :to="to"
        :class="['btn', variantClass, sizeClass, block && 'btn--block', icon && 'btn--icon']"
    >
        <slot />
    </NuxtLink>
    <a
        v-else-if="href"
        :href="href"
        :target="target"
        :rel="rel"
        :class="['btn', variantClass, sizeClass, block && 'btn--block', icon && 'btn--icon']"
    >
        <slot />
    </a>
    <button
        v-else
        :type="type"
        :disabled="disabled || loading"
        :aria-busy="loading || undefined"
        :class="['btn', variantClass, sizeClass, block && 'btn--block', icon && 'btn--icon']"
    >
        <span v-if="loading" class="btn-spinner" aria-hidden="true" />
        <slot />
    </button>
</template>
