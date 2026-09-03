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

