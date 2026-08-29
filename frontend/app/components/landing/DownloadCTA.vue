<script setup lang="ts">
/**
 * <DownloadCTA />
 *
 * Final section. App Store + Google Play store badges. Clicks ping
 * /api/v1/analytics/click via sendBeacon.
 */
const { t } = useI18n();
const config = useRuntimeConfig();
const appStoreUrl = config.public.appStoreUrl as string;
const playStoreUrl = config.public.playStoreUrl as string;
const laravel = (config.public.laravelUrl as string).replace(/\/+$/, '');

const track = useClickTracker();

const onClick = (target: 'app_store' | 'play_store') => {
    track(target, 'landing');
};
</script>

<template>
    <section id="download" class="py-24 sm:py-32 hero-wash">
        <div class="max-w-3xl mx-auto px-5 sm:px-8 text-center">
            <h2 class="font-display text-3xl sm:text-5xl font-bold text-[#130e21] mb-5 leading-tight">
                {{ t('landing.final_title') }}
            </h2>
            <p class="text-lg text-[#4a4458] mb-8 max-w-xl mx-auto">
                {{ t('landing.final_subtitle') }}
            </p>
            <div class="store-badges mb-5">
                <a
                    :href="appStoreUrl"
                    class="store-badge store-badge-apple"
                    target="_blank"
                    rel="noopener"
                    :aria-label="`${t('landing.store_download_on')} ${t('landing.store_app_store')}`"
                    data-track-click="app_store"
                    data-track-page="landing"
                    @click="onClick('app_store')"
                >
                    <img :src="`${laravel}/images/app-store.svg`" alt="" class="store-badge-icon" width="26" height="26" aria-hidden="true" />
                    <span class="store-badge-text">
                        <span class="store-badge-caption">{{ t('landing.store_download_on') }}</span>
                        <span class="store-badge-label">{{ t('landing.store_app_store') }}</span>
                    </span>
                </a>
                <a
                    :href="playStoreUrl"
                    class="store-badge store-badge-google"
                    target="_blank"
                    rel="noopener"
                    :aria-label="`${t('landing.store_get_it_on')} ${t('landing.store_play_store')}`"
                    data-track-click="play_store"
                    data-track-page="landing"
                    @click="onClick('play_store')"
                >
                    <img :src="`${laravel}/images/google-play.svg`" alt="" class="store-badge-icon" width="24" height="24" aria-hidden="true" />
                    <span class="store-badge-text">
                        <span class="store-badge-caption">{{ t('landing.store_get_it_on') }}</span>
                        <span class="store-badge-label">{{ t('landing.store_play_store') }}</span>
                    </span>
                </a>
            </div>
            <p class="mt-5 text-sm text-[#8a8499]">{{ t('landing.final_note') }}</p>
        </div>
    </section>
</template>
