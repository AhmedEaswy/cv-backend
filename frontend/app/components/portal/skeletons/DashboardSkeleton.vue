<script setup lang="ts">
defineProps<{ rows?: number }>();
</script>

<template>
    <div class="skeleton-page" role="status" aria-live="polite" aria-busy="true">
        <div class="stats-grid">
            <div v-for="n in 4" :key="n" class="skeleton-stat">
                <div class="stat__top">
                    <Skeleton width="4.5rem" height="0.65rem" />
                    <Skeleton width="30px" height="30px" radius="8px" />
                </div>
                <Skeleton width="3rem" height="2.2rem" radius="10px" />
                <Skeleton width="6.5rem" height="0.7rem" />
            </div>
        </div>

        <div class="dash-layout">
            <article class="surface dash-card">
                <header class="dash-card__head">
                    <div class="skeleton-stack" style="flex: 1">
                        <Skeleton width="9rem" height="1.25rem" />
                        <Skeleton width="14rem" height="0.75rem" />
                    </div>
                    <Skeleton width="4.5rem" height="1.75rem" radius="9999px" />
                </header>
                <div class="dash-filters" style="display: flex; gap: 0.35rem; margin-bottom: 1rem">
                    <Skeleton v-for="n in 4" :key="n" width="4.2rem" height="1.85rem" radius="9999px" />
                </div>
                <div>
                    <div v-for="n in (rows ?? 5)" :key="n" class="skeleton-recent-row">
                        <Skeleton width="38px" height="38px" radius="8px" />
                        <div class="skeleton-stack" style="flex: 1">
                            <Skeleton width="55%" height="0.9rem" />
                            <Skeleton width="35%" height="0.7rem" />
                        </div>
                        <Skeleton width="3.5rem" height="1.35rem" radius="9999px" class="hidden sm:inline-block" />
                        <Skeleton width="16px" height="16px" />
                    </div>
                </div>
            </article>

            <aside class="dash-rail">
                <div v-for="n in 3" :key="n" class="skeleton-rail-card">
                    <Skeleton width="7rem" height="0.95rem" />
                    <Skeleton width="100%" height="0.75rem" />
                    <Skeleton width="85%" height="0.75rem" />
                    <Skeleton width="100%" height="2.25rem" radius="9999px" />
                </div>
            </aside>
        </div>
        <span class="sr-only">Loading</span>
    </div>
</template>
