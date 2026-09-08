<script setup lang="ts">
withDefaults(defineProps<{
    fields?: number;
    wide?: boolean;
    withHeader?: boolean;
}>(), {
    fields: 5,
    wide: false,
    withHeader: false,
});
</script>

<template>
    <div
        class="skeleton-page"
        role="status"
        aria-live="polite"
        aria-busy="true"
    >
        <header v-if="withHeader" class="page-header">
            <div class="skeleton-stack" style="gap: 0.55rem">
                <Skeleton width="6.5rem" height="0.75rem" />
                <Skeleton width="16rem" height="1.85rem" />
                <Skeleton width="12rem" height="0.8rem" />
            </div>
            <div class="page-header__actions">
                <Skeleton width="2.5rem" height="2.5rem" radius="9999px" />
            </div>
        </header>

        <div
            class="skeleton-form-card"
            :class="wide ? 'form-card--wide' : 'form-card--xl'"
        >
            <div class="skeleton-stack" style="margin-bottom: 0.35rem">
                <Skeleton width="8rem" height="1.15rem" />
                <Skeleton width="16rem" height="0.75rem" />
            </div>

            <div v-for="n in fields" :key="n" class="skeleton-field">
                <Skeleton width="5.5rem" height="0.7rem" />
                <Skeleton width="100%" height="2.6rem" radius="10px" />
                <Skeleton v-if="n % 2 === 1" width="12rem" height="0.65rem" />
            </div>

            <div style="display: flex; gap: 0.5rem; justify-content: flex-end; margin-top: 0.35rem">
                <Skeleton width="6.5rem" height="2.4rem" radius="9999px" />
            </div>
        </div>
        <span class="sr-only">Loading</span>
    </div>
</template>
