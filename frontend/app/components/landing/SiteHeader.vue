<script setup lang="ts">
/**
 * <LandingHeader />
 *
 * Fixed floating nav over the dark hero. On SSR we hit /api/v1/auth/me through
 * the forwarded cookie so we know whether to show "Open the portal" or "Login".
 */
const { t } = useI18n();
const config = useRuntimeConfig();
const appName = config.public.appName as string;
const laravel = (config.public.laravelUrl as string).replace(/\/+$/, '');
const playStore = (config.public.playStoreUrl as string) || '#download';

const { user } = await useAuth();
const route = useRoute();

const logoSrc = `${laravel}/images/logo-horizontal-white.png`;
const scrolled = ref(false);
const onHome = computed(() => route.path === '/' || route.path === '');

/** Section anchors work from any page via `/#…`. */
const section = (id: string) => (onHome.value ? `#${id}` : `/#${id}`);

const onScroll = () => {
    scrolled.value = window.scrollY > 24;
};

onMounted(() => {
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
});

onBeforeUnmount(() => {
    window.removeEventListener('scroll', onScroll);
});

const trackDownload = () => {
    if (!import.meta.client) return;
    const api = useApi();
    api('/analytics/click', { method: 'POST', body: { label: 'nav_download_app', page: 'landing' } }).catch(
        () => undefined,
    );
};
</script>

<template>
    <header
        class="site-header"
        :class="{ 'site-header--scrolled': scrolled || !onHome }"
    >
        <div class="site-header__bar">
            <a :href="laravel + '/'" class="site-header__brand" :aria-label="`${appName} — home`">
                <img
                    :src="logoSrc"
                    :alt="appName"
                    class="site-header__logo"
                    @error="(($event.target as HTMLImageElement).src = `${laravel}/images/logo-icon.png`)"
                />
            </a>

            <nav class="site-header__nav" aria-label="Primary">
                <a :href="section('platforms')">{{ t('landing.nav.platforms') }}</a>
                <a :href="section('ai-connect')">{{ t('landing.nav.ai_connect') }}</a>
                <NuxtLink to="/templates">{{ t('landing.nav.templates') }}</NuxtLink>
                <a :href="section('mockup')">{{ t('landing.nav.mockup') }}</a>
                <a :href="section('pricing')">{{ t('landing.nav.pricing') }}</a>
                <a :href="section('download')">{{ t('landing.nav.download') }}</a>
            </nav>

            <div class="site-header__actions">
                <LangSwitcher />

                <NuxtLink
                    v-if="user"
                    to="/portal"
                    class="site-header__ghost"
                >
                    {{ t('landing.nav.dashboard') }}
                </NuxtLink>
                <NuxtLink
                    v-else
                    to="/auth/login"
                    class="site-header__ghost"
                    :aria-label="t('landing.nav.login')"
                >
                    {{ t('landing.nav.login') }}
                </NuxtLink>

                <a
                    :href="playStore"
                    class="site-header__cta"
                    target="_blank"
                    rel="noopener"
                    @click="trackDownload"
                >
                    {{ t('landing.nav.cta') }}
                </a>
            </div>
        </div>
    </header>
</template>

