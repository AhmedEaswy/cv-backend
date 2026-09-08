<template>
    <div class="skeleton-page" role="status" aria-live="polite" aria-busy="true">
        <header class="page-header">
            <div class="skeleton-stack" style="gap: 0.55rem">
                <Skeleton width="6.5rem" height="0.75rem" />
                <Skeleton width="16rem" height="1.85rem" />
                <Skeleton width="12rem" height="0.8rem" />
            </div>
            <div class="page-header__actions">
                <Skeleton width="2.5rem" height="2.5rem" radius="9999px" />
                <Skeleton width="7.5rem" height="2.5rem" radius="9999px" />
                <Skeleton width="6.5rem" height="2.5rem" radius="9999px" />
                <Skeleton width="7.25rem" height="2.5rem" radius="9999px" />
            </div>
        </header>

        <div class="cv-builder">
            <div class="cv-builder__main">
                <div class="cv-hub">
                    <div class="cv-hub__section">
                        <div class="cv-hub__toggle" style="cursor: default">
                            <div class="skeleton-stack" style="flex: 1; gap: 0.4rem">
                                <Skeleton width="9rem" height="0.95rem" />
                                <Skeleton width="6rem" height="0.7rem" />
                            </div>
                            <Skeleton width="16px" height="16px" />
                        </div>
                        <div class="cv-hub__body">
                            <div class="cv-fields">
                                <div v-for="n in 6" :key="n" class="skeleton-field" :class="{ 'field--full': n > 4 }">
                                    <Skeleton width="5rem" height="0.7rem" />
                                    <Skeleton width="100%" :height="n === 6 ? '5.5rem' : '2.6rem'" radius="10px" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-for="n in 7" :key="n" class="cv-hub__section">
                        <div class="cv-hub__toggle" style="cursor: default">
                            <div class="skeleton-stack" style="flex: 1; gap: 0.4rem">
                                <Skeleton width="8rem" height="0.95rem" />
                                <Skeleton width="4.5rem" height="0.7rem" />
                            </div>
                            <Skeleton width="16px" height="16px" />
                        </div>
                    </div>
                </div>
            </div>

            <aside class="cv-builder__side">
                <div class="skeleton-form-card cv-builder__meta">
                    <div v-for="n in 4" :key="n" class="skeleton-field">
                        <Skeleton width="5.5rem" height="0.7rem" />
                        <Skeleton width="100%" height="2.6rem" radius="10px" />
                    </div>
                </div>
            </aside>

            <div class="cv-builder__save">
                <Skeleton width="8.5rem" height="2.5rem" radius="9999px" />
            </div>
        </div>
        <span class="sr-only">Loading</span>
    </div>
</template>
