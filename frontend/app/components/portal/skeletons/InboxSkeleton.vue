<template>
    <div class="inbox-layout" role="status" aria-live="polite" aria-busy="true">
        <div class="inbox-list">
            <div v-for="n in 6" :key="n" class="skeleton-inbox-item">
                <div style="display: flex; justify-content: space-between; gap: 0.75rem">
                    <Skeleton width="45%" height="0.85rem" />
                    <Skeleton width="4rem" height="0.7rem" />
                </div>
                <Skeleton width="75%" height="0.75rem" />
            </div>
        </div>
        <article class="surface skeleton-inbox-detail">
            <div style="display: flex; justify-content: space-between; gap: 1rem">
                <div class="skeleton-stack" style="flex: 1">
                    <Skeleton width="10rem" height="1.4rem" />
                    <Skeleton width="12rem" height="0.75rem" />
                </div>
                <Skeleton width="3.5rem" height="1.4rem" radius="9999px" />
            </div>
            <Skeleton width="55%" height="0.95rem" />
            <Skeleton width="100%" height="0.8rem" />
            <Skeleton width="100%" height="0.8rem" />
            <Skeleton width="92%" height="0.8rem" />
            <Skeleton width="70%" height="0.8rem" />
            <Skeleton width="7.5rem" height="2.35rem" radius="9999px" style="margin-top: auto" />
        </article>
        <span class="sr-only">Loading</span>
    </div>
</template>
