<script setup lang="ts">
/**
 * <Tag variant="default | outline | soft | success | warning | danger">
 * Matches the small pill labels in the reference ("Responsive", "under 3 seconds").
 */
withDefaults(defineProps<{
    variant?: 'default' | 'outline' | 'soft' | 'success' | 'warning' | 'danger';
}>(), { variant: 'default' });
</script>

<template>
    <span :class="['tag', variant !== 'default' && `tag--${variant}`]">
        <slot />
    </span>
</template>
