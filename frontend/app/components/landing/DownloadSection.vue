<script setup lang="ts">
/**
 * <LandingDownloadSection /> — dark closing CTA with App Store + Play Store badges.
 */
const { t } = useI18n();
const config = useRuntimeConfig();
const laravel = (config.public.laravelUrl as string).replace(/\/+$/, '');
const appStore = (config.public.appStoreUrl as string) || '#';
const playStore = (config.public.playStoreUrl as string) || '#';

const track = (label: string) => {
    if (!import.meta.client) return;
    const api = useApi();
    api('/analytics/click', { method: 'POST', body: { label, page: 'landing' } }).catch(() => undefined);
};
</script>

<template>
    <section id="download" class="closing-cta" aria-labelledby="download-title">
        <div class="closing-cta__panel">
            <div class="closing-cta__glow" aria-hidden="true" />

            <h2 id="download-title" class="closing-cta__title">
                {{ t('landing.final_title') }}
            </h2>
            <p class="closing-cta__subtitle">
                {{ t('landing.final_subtitle') }}
            </p>

            <div class="closing-cta__badges">
                <a
                    :href="appStore"
                    class="closing-cta__badge"
                    target="_blank"
                    rel="noopener"
                    :aria-label="`${t('landing.store_download_on')} ${t('landing.store_app_store')}`"
                    @click="track('app_store')"
                >
                    <img
                        :src="`${laravel}/images/app-store.svg`"
                        alt=""
                        width="22"
                        height="22"
                        aria-hidden="true"
                    />
                    <span class="closing-cta__badge-text">
                        <span class="closing-cta__badge-caption">{{ t('landing.store_download_on') }}</span>
                        <span class="closing-cta__badge-label">{{ t('landing.store_app_store') }}</span>
                    </span>
                </a>
                <a
                    :href="playStore"
                    class="closing-cta__badge"
                    target="_blank"
                    rel="noopener"
                    :aria-label="`${t('landing.store_get_it_on')} ${t('landing.store_play_store')}`"
                    @click="track('play_store')"
                >
                    <img
                        :src="`${laravel}/images/google-play.svg`"
                        alt=""
                        width="22"
                        height="22"
                        aria-hidden="true"
                    />
                    <span class="closing-cta__badge-text">
                        <span class="closing-cta__badge-caption">{{ t('landing.store_get_it_on') }}</span>
                        <span class="closing-cta__badge-label">{{ t('landing.store_play_store') }}</span>
                    </span>
                </a>
            </div>

            <p class="closing-cta__note">{{ t('landing.final_note') }}</p>
        </div>
    </section>
</template>

<style scoped>
.closing-cta__panel {
    position: relative;
    overflow: hidden;
    margin-inline: auto;
    padding: clamp(3rem, 8vw, 4.5rem) 1.5rem;
    border-radius: 1.35rem;
    background:
        radial-gradient(ellipse 80% 60% at 50% 0%, rgba(255, 255, 255, 0.07), transparent 65%),
        linear-gradient(180deg, #121212 0%, #0a0a0a 100%);
    border: 1px solid rgba(255, 255, 255, 0.06);
    text-align: center;
    isolation: isolate;
}

@media (min-width: 768px) {
    .closing-cta__panel {
        border-radius: 1.5rem;
        padding: clamp(3.5rem, 8vw, 5rem) 2.5rem;
    }
}

.closing-cta__glow {
    pointer-events: none;
    position: absolute;
    inset: -30% -10% auto;
    height: 70%;
    background: radial-gradient(ellipse 50% 40% at 50% 0%, rgba(255, 255, 255, 0.08), transparent 70%);
    z-index: 0;
}

.closing-cta__title {
    position: relative;
    z-index: 1;
    margin: 0 auto 1rem;
    max-width: 16ch;
    font-family: var(--font-display);
    font-size: clamp(2rem, 5vw, 3rem);
    line-height: 1.08;
    letter-spacing: -0.03em;
    font-weight: 400;
    color: #ffffff;
    text-wrap: balance;
}

.closing-cta__subtitle {
    position: relative;
    z-index: 1;
    margin: 0 auto 2rem;
    max-width: 34rem;
    font-size: 1rem;
    line-height: 1.6;
    color: rgba(255, 255, 255, 0.55);
}

.closing-cta__badges {
    position: relative;
    z-index: 1;
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    justify-content: center;
}

.closing-cta__badge {
    display: inline-flex;
    align-items: center;
    gap: 0.65rem;
    padding: 0.65rem 1.15rem 0.65rem 0.9rem;
    border-radius: var(--radius-pill);
    background: #f4f4f2;
    color: #0a0a0a;
    text-decoration: none;
    border: 1px solid rgba(255, 255, 255, 0.12);
    box-shadow: 0 12px 32px -12px rgba(0, 0, 0, 0.45);
    transition: background 0.15s ease, transform 0.15s ease, box-shadow 0.15s ease;
}

.closing-cta__badge:hover {
    background: #ffffff;
    transform: translateY(-1px);
    box-shadow: 0 16px 36px -12px rgba(0, 0, 0, 0.5);
}

.closing-cta__badge img {
    flex-shrink: 0;
    display: block;
}

.closing-cta__badge-text {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    line-height: 1.1;
    text-align: start;
}

.closing-cta__badge-caption {
    font-size: 0.65rem;
    font-weight: 500;
    letter-spacing: 0.01em;
    color: rgba(10, 10, 10, 0.65);
}

.closing-cta__badge-label {
    font-size: 0.95rem;
    font-weight: 600;
    letter-spacing: -0.01em;
}

.closing-cta__note {
    position: relative;
    z-index: 1;
    margin: 1.25rem 0 0;
    font-size: 0.85rem;
    color: rgba(255, 255, 255, 0.38);
}
</style>
