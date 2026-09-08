<script setup lang="ts">
withDefaults(defineProps<{ rows?: number }>(), { rows: 5 });
</script>

<template>
    <div class="list skeleton-page" role="status" aria-live="polite" aria-busy="true">
        <article v-for="n in rows" :key="n" class="skeleton-list-card">
            <Skeleton width="38px" height="38px" radius="8px" />
            <div class="skeleton-stack" style="flex: 1; min-width: 0">
                <Skeleton width="42%" height="0.95rem" />
                <Skeleton width="28%" height="0.7rem" />
            </div>
            <Skeleton width="3.5rem" height="1.35rem" radius="9999px" />
            <Skeleton width="4.5rem" height="2rem" radius="9999px" />
            <Skeleton width="2rem" height="2rem" radius="8px" />
        </article>
        <span class="sr-only">Loading</span>
    </div>
</template>
