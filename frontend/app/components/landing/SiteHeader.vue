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

const logoSrc = `${laravel}/images/logo-horizontal-white.png`;
const scrolled = ref(false);

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
        :class="{ 'site-header--scrolled': scrolled }"
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
                <a href="#platforms">{{ t('landing.nav.platforms') }}</a>
                <a href="#ai-connect">{{ t('landing.nav.ai_connect') }}</a>
                <a href="#mockup">{{ t('landing.nav.mockup') }}</a>
                <a href="#pricing">{{ t('landing.nav.pricing') }}</a>
                <a href="#download">{{ t('landing.nav.download') }}</a>
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

<style scoped>
.site-header {
    position: fixed;
    top: 0.75rem;
    left: 0;
    right: 0;
    z-index: 40;
    padding: 0.85rem 0.75rem 0;
    pointer-events: none;
}

@media (min-width: 768px) {
    .site-header {
        top: 1rem;
        padding: 1rem 1rem 0;
    }
}

.site-header__bar {
    pointer-events: auto;
    max-width: 72rem;
    margin-inline: auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    min-height: 3.25rem;
    padding: 0.45rem 0.55rem 0.45rem 0.9rem;
    border-radius: 9999px;
    background: rgba(12, 12, 12, 0.55);
    border: 1px solid rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    box-shadow: 0 10px 40px -20px rgba(0, 0, 0, 0.6);
    transition: background 0.2s ease, border-color 0.2s ease;
}

.site-header--scrolled .site-header__bar {
    background: rgba(8, 8, 8, 0.82);
    border-color: rgba(255, 255, 255, 0.12);
}

.site-header__brand {
    display: flex;
    align-items: center;
    flex-shrink: 0;
}

.site-header__logo {
    height: 1.55rem;
    width: auto;
}

@media (min-width: 640px) {
    .site-header__logo {
        height: 1.75rem;
    }
}

.site-header__nav {
    display: none;
    align-items: center;
    gap: 1.6rem;
    font-size: 0.875rem;
    color: rgba(255, 255, 255, 0.72);
}

.site-header__nav a {
    text-decoration: none;
    color: inherit;
    transition: color 0.15s ease;
}

.site-header__nav a:hover {
    color: #ffffff;
}

@media (min-width: 768px) {
    .site-header__nav {
        display: flex;
    }
}

.site-header__actions {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    flex-shrink: 0;
}

.site-header__ghost {
    display: none;
    align-items: center;
    padding: 0.45rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.85rem;
    font-weight: 500;
    color: rgba(255, 255, 255, 0.78);
    text-decoration: none;
    transition: color 0.15s ease, background 0.15s ease;
}

.site-header__ghost:hover {
    color: #fff;
    background: rgba(255, 255, 255, 0.06);
}

@media (min-width: 640px) {
    .site-header__ghost {
        display: inline-flex;
    }
}

.site-header__cta {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.55rem 1rem;
    border-radius: 9999px;
    font-size: 0.85rem;
    font-weight: 500;
    color: #0a0a0a;
    background: #f4f4f2;
    text-decoration: none;
    white-space: nowrap;
    transition: background 0.15s ease, transform 0.15s ease;
}

.site-header__cta:hover {
    background: #ffffff;
    transform: translateY(-1px);
}

/* Lang switcher contrast on dark bar */
.site-header__actions :deep(.lang-trigger) {
    color: rgba(255, 255, 255, 0.8);
}
.site-header__actions :deep(.lang-trigger:hover),
.site-header__actions :deep(.lang-trigger[aria-expanded='true']) {
    color: #0a0a0a;
    background: #f4f4f2;
    border-color: transparent;
}
</style>
