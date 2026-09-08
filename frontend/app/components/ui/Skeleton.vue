<script setup lang="ts">
/**
 * <Skeleton /> — shimmer placeholder block.
 * Use width/height (CSS length) or class for layout.
 */
withDefaults(defineProps<{
    width?: string;
    height?: string;
    radius?: string;
    circle?: boolean;
    class?: string;
}>(), {
    width: '100%',
    height: '0.9rem',
    radius: '8px',
});
</script>

<template>
    <span
        class="skeleton"
        :class="[$props.class, circle && 'skeleton--circle']"
        :style="{
            width: circle ? height : width,
            height,
            borderRadius: circle ? '9999px' : radius,
        }"
        aria-hidden="true"
    />
</template>
