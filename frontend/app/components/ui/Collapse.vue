<script setup lang="ts">
/**
 * Height collapse matching Alpine.js x-collapse (grid 0fr ↔ 1fr).
 */
const props = withDefaults(defineProps<{
    open: boolean;
    duration?: number;
}>(), {
    duration: 250,
});
</script>

<template>
    <div
        class="ui-collapse"
        :class="{ 'is-open': props.open }"
        :style="{ '--collapse-duration': `${props.duration}ms` }"
    >
        <div class="ui-collapse__inner">
            <slot />
        </div>
    </div>
</template>
