<script setup lang="ts">
/**
 * <DownloadCTA />
 *
 * Final section. App Store + Google Play store badges.
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
    <section id="download" class="download-cta hero-wash">
        <div class="download-cta__inner">
            <h2 class="download-cta__title">
                {{ t('landing.final_title') }}
            </h2>
            <p class="download-cta__subtitle">
                {{ t('landing.final_subtitle') }}
            </p>
            <div class="store-badges">
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
            <p class="download-cta__note">{{ t('landing.final_note') }}</p>
        </div>
    </section>
</template>
