<template>
    <div class="skeleton-page" role="status" aria-live="polite" aria-busy="true">
        <div class="skeleton-form-card form-card--xl">
            <div class="skeleton-stack" style="margin-bottom: 0.35rem">
                <Skeleton width="8rem" height="1.15rem" />
                <Skeleton width="18rem" height="0.75rem" />
            </div>
            <div v-for="n in 4" :key="`a-${n}`" class="skeleton-field">
                <Skeleton width="5.5rem" height="0.7rem" />
                <Skeleton width="100%" height="2.6rem" radius="10px" />
            </div>
            <div style="display: flex; justify-content: flex-end">
                <Skeleton width="7.5rem" height="2.4rem" radius="9999px" />
            </div>
        </div>

        <div class="skeleton-form-card form-card--xl">
            <div class="skeleton-stack" style="margin-bottom: 0.35rem">
                <Skeleton width="7rem" height="1.15rem" />
                <Skeleton width="20rem" height="0.75rem" />
            </div>
            <div class="skeleton-field">
                <Skeleton width="4rem" height="0.7rem" />
                <Skeleton width="100%" height="2.6rem" radius="10px" />
            </div>
            <Skeleton width="8rem" height="2.4rem" radius="9999px" />
            <div class="skeleton-stack" style="margin-top: 0.5rem">
                <div v-for="n in 2" :key="`t-${n}`" class="skeleton-list-card" style="padding: 0.85rem 1rem">
                    <div class="skeleton-stack" style="flex: 1">
                        <Skeleton width="40%" height="0.85rem" />
                        <Skeleton width="25%" height="0.7rem" />
                    </div>
                    <Skeleton width="4.5rem" height="2rem" radius="9999px" />
                </div>
            </div>
        </div>
        <span class="sr-only">Loading</span>
    </div>
</template>
