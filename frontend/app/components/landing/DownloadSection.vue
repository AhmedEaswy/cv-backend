<script setup lang="ts">
/**
 * <LandingDownload /> — "Available on iOS & Android" section.
 */
const { t } = useI18n();
const config = useRuntimeConfig();
const appStore = (config.public.appStoreUrl as string) || '#';
const playStore = (config.public.playStoreUrl as string) || '#';

const track = (label: string) => {
    if (import.meta.client) {
        const api = useApi();
        api('/analytics/click', { method: 'POST', body: { label, page: 'landing' } }).catch(() => undefined);
    }
};
</script>

<template>
    <section id="download" class="section download-section">
        <div class="container-narrow download">
            <div class="download__copy">
                <h2 class="display-2">{{ t('landing.section_download_title') }}</h2>
                <p class="lede">{{ t('landing.section_download_subtitle') }}</p>
            </div>
            <div class="download__badges">
                <a :href="appStore" target="_blank" rel="noopener" class="store-badge" @click="track('app_store')">
                    <svg class="store-badge-icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M17.05 13.06c-.03-2.7 2.21-3.99 2.31-4.06-1.26-1.84-3.22-2.09-3.92-2.12-1.67-.17-3.25.98-4.1.98-.85 0-2.16-.96-3.55-.93-1.82.03-3.5 1.06-4.43 2.68-1.89 3.28-.48 8.13 1.36 10.79.9 1.3 1.97 2.76 3.36 2.71 1.35-.05 1.86-.87 3.49-.87 1.62 0 2.09.87 3.52.84 1.46-.02 2.38-1.32 3.27-2.63 1.03-1.5 1.45-2.97 1.48-3.05-.03-.01-2.84-1.09-2.87-4.32zM14.65 5.21c.74-.9 1.24-2.14 1.1-3.38-1.06.04-2.35.71-3.11 1.6-.68.79-1.28 2.05-1.12 3.27 1.19.09 2.39-.6 3.13-1.49z" />
                    </svg>
                    <div class="store-badge-text">
                        <span class="store-badge-caption">{{ t('landing.section_app_store_caption') }}</span>
                        <span class="store-badge-label">{{ t('landing.section_app_store') }}</span>
                    </div>
                </a>
                <a :href="playStore" target="_blank" rel="noopener" class="store-badge store-badge-google" @click="track('play_store')">
                    <svg class="store-badge-icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M3.5 2.5v19l9-9.5z" />
                        <path d="M3.5 2.5l13 7.5-4 2.5z" opacity="0.7" />
                        <path d="M3.5 21.5l13-7.5-4-2.5z" opacity="0.5" />
                        <path d="M16.5 10l4 2.3c.9.5.9 1.9 0 2.4l-4 2.3-3.5-3.5z" opacity="0.4" />
                    </svg>
                    <div class="store-badge-text">
                        <span class="store-badge-caption">{{ t('landing.section_play_store_caption') }}</span>
                        <span class="store-badge-label">{{ t('landing.section_play_store') }}</span>
                    </div>
                </a>
            </div>
        </div>
    </section>
</template>

<style scoped>
.download-section { background: var(--color-paper-2); border-top: 1px solid var(--color-line); border-bottom: 1px solid var(--color-line); }
.download {
    display: grid;
    grid-template-columns: 1fr;
    gap: 2rem;
    align-items: center;
    text-align: center;
}
@media (min-width: 768px) {
    .download {
        grid-template-columns: 1.2fr 1fr;
        text-align: start;
        gap: 4rem;
    }
}
.download__copy .display-2 { margin: 0 0 0.75rem; }
.download__copy .lede { margin: 0; }
.download__badges {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    justify-content: center;
}
@media (min-width: 768px) { .download__badges { justify-content: flex-start; } }
</style>
