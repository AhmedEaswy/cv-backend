<script setup lang="ts">
/**
 * <Button variant="primary" size="md" :loading="isSubmitting" />
 * Renders as <a> when `to` is provided, otherwise as <button>.
 */
withDefaults(defineProps<{
    variant?: 'primary' | 'secondary' | 'ghost' | 'danger' | 'link';
    size?: 'sm' | 'md' | 'lg';
    type?: 'button' | 'submit' | 'reset';
    to?: string;
    href?: string;
    loading?: boolean;
    disabled?: boolean;
    block?: boolean;
    target?: string;
    rel?: string;
}>(), {
    variant: 'primary',
    size: 'md',
    type: 'button',
});

const sizeClass = (size: 'sm' | 'md' | 'lg') => size === 'sm' ? 'btn--sm' : size === 'lg' ? 'btn--lg' : '';
const variantClass = (v: string) => v === 'primary' ? 'btn--primary' : v === 'secondary' ? 'btn--secondary' : v === 'ghost' ? 'btn--ghost' : v === 'danger' ? 'btn--danger' : v === 'link' ? 'btn--link' : '';
</script>

<template>
    <NuxtLink
        v-if="to"
        :to="to"
        :class="['btn', variantClass(variant), sizeClass(size), block && 'btn--block']"
    >
        <slot />
    </NuxtLink>
    <a
        v-else-if="href"
        :href="href"
        :target="target"
        :rel="rel"
        :class="['btn', variantClass(variant), sizeClass(size), block && 'btn--block']"
    >
        <slot />
    </a>
    <button
        v-else
        :type="type"
        :disabled="disabled || loading"
        :aria-busy="loading || undefined"
        :class="['btn', variantClass(variant), sizeClass(size), block && 'btn--block']"
    >
        <span v-if="loading" class="btn-spinner" aria-hidden="true" />
        <slot />
    </button>
</template>

