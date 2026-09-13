<script setup lang="ts">
/**
 * <LandingHeader />
 *
 * Fixed floating nav over the dark hero. On SSR we hit /api/v1/auth/me through
 * the forwarded cookie so we know whether to show "Open the portal" or "Login".
 *
 * Desktop (≥xl): full inline nav. Below xl (phones + iPads): hamburger opens a
 * submenu panel so the bar never overflows the viewport.
 */
const { t } = useI18n();
const config = useRuntimeConfig();
const appName = config.public.appName as string;
const laravel = (config.public.laravelUrl as string).replace(/\/+$/, '');
const playStore = (config.public.playStoreUrl as string) || '#download';

const { user } = await useAuthUser();
const route = useRoute();

const logoSrc = `${laravel}/images/logo-horizontal-white.png`;
const scrolled = ref(false);
const onHome = computed(() => route.path === '/' || route.path === '');
const activeSection = ref<string | null>(null);
const menuOpen = ref(false);

/** Inline nav only when there is room for all links + actions (iPad needs the submenu). */
const INLINE_NAV_MQ = '(min-width: 1280px)';
const isInlineNav = ref(false);

const navSections = ['platforms', 'ai-connect', 'mockup', 'pricing', 'download'] as const;

/** Section anchors work from any page via `/#…`. */
const section = (id: string) => (onHome.value ? `#${id}` : `/#${id}`);

const isTemplatesActive = computed(() => route.path === '/templates' || route.path.startsWith('/templates/'));

function isSectionActive(id: string) {
    return onHome.value && activeSection.value === id;
}

const onScroll = () => {
    scrolled.value = window.scrollY > 24;
    if (!onHome.value) {
        activeSection.value = null;
        return;
    }

    const offset = window.innerHeight * 0.28;
    let current: string | null = null;
    for (const id of navSections) {
        const el = document.getElementById(id);
        if (!el) continue;
        const top = el.getBoundingClientRect().top;
        if (top - offset <= 0) current = id;
    }
    activeSection.value = current ?? navSections[0];
};

function syncNavMode() {
    if (!import.meta.client) return;
    isInlineNav.value = window.matchMedia(INLINE_NAV_MQ).matches;
    if (isInlineNav.value) {
        setMenuOpen(false);
    }
}

function setMenuOpen(open: boolean) {
    menuOpen.value = open;
    if (import.meta.client) {
        document.body.style.overflow = open ? 'hidden' : '';
    }
}

function toggleMenu() {
    setMenuOpen(!menuOpen.value);
}

function closeMenu() {
    setMenuOpen(false);
}

const trackDownload = () => {
    if (!import.meta.client) return;
    const api = useApi();
    api('/analytics/click', { method: 'POST', body: { label: 'nav_download_app', page: 'landing' } }).catch(
        () => undefined,
    );
};

function onCtaPointerMove(e: PointerEvent) {
    const el = e.currentTarget as HTMLElement;
    const rect = el.getBoundingClientRect();
    el.style.setProperty('--btn-spot-x', `${((e.clientX - rect.left) / rect.width) * 100}%`);
    el.style.setProperty('--btn-spot-y', `${((e.clientY - rect.top) / rect.height) * 100}%`);
}

function onCtaPointerLeave(e: PointerEvent) {
    const el = e.currentTarget as HTMLElement;
    el.style.removeProperty('--btn-spot-x');
    el.style.removeProperty('--btn-spot-y');
}

function onKeydown(e: KeyboardEvent) {
    if (e.key === 'Escape') closeMenu();
}

onMounted(() => {
    onScroll();
    syncNavMode();
    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', syncNavMode);
    document.addEventListener('keydown', onKeydown);
});

onBeforeUnmount(() => {
    window.removeEventListener('scroll', onScroll);
    window.removeEventListener('resize', syncNavMode);
    document.removeEventListener('keydown', onKeydown);
    document.body.style.overflow = '';
});

watch(onHome, () => {
    if (import.meta.client) onScroll();
});

watch(
    () => route.fullPath,
    () => closeMenu(),
);
</script>

<template>
    <header
        class="site-header"
        :class="{
            'site-header--scrolled': scrolled || !onHome,
            'site-header--menu-open': menuOpen,
        }"
    >
        <div class="site-header__shell">
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
                    <a
                        :href="section('platforms')"
                        :class="{ 'is-active': isSectionActive('platforms') }"
                        :aria-current="isSectionActive('platforms') ? 'true' : undefined"
                    >{{ t('landing.nav.platforms') }}</a>
                    <a
                        :href="section('ai-connect')"
                        :class="{ 'is-active': isSectionActive('ai-connect') }"
                        :aria-current="isSectionActive('ai-connect') ? 'true' : undefined"
                    >{{ t('landing.nav.ai_connect') }}</a>
                    <NuxtLink
                        to="/templates"
                        :class="{ 'is-active': isTemplatesActive }"
                        :aria-current="isTemplatesActive ? 'page' : undefined"
                    >{{ t('landing.nav.templates') }}</NuxtLink>
                    <a
                        :href="section('mockup')"
                        :class="{ 'is-active': isSectionActive('mockup') }"
                        :aria-current="isSectionActive('mockup') ? 'true' : undefined"
                    >{{ t('landing.nav.mockup') }}</a>
                    <a
                        :href="section('pricing')"
                        :class="{ 'is-active': isSectionActive('pricing') }"
                        :aria-current="isSectionActive('pricing') ? 'true' : undefined"
                    >{{ t('landing.nav.pricing') }}</a>
                    <a
                        :href="section('download')"
                        :class="{ 'is-active': isSectionActive('download') }"
                        :aria-current="isSectionActive('download') ? 'true' : undefined"
                    >{{ t('landing.nav.download') }}</a>
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
                        @pointermove="onCtaPointerMove"
                        @pointerleave="onCtaPointerLeave"
                        @click="trackDownload"
                    >
                        <span>{{ t('landing.nav.cta') }}</span>
                    </a>

                    <button
                        type="button"
                        class="site-header__menu-btn"
                        :aria-expanded="menuOpen"
                        aria-controls="site-header-menu"
                        :aria-label="menuOpen ? t('portal.nav.close_menu') : t('portal.nav.menu')"
                        @click.stop="toggleMenu"
                    >
                        <Icon :name="menuOpen ? 'close' : 'menu'" :size="18" />
                    </button>
                </div>
            </div>

            <div
                v-show="menuOpen"
                id="site-header-menu"
                class="site-header__panel"
                role="dialog"
                :aria-label="t('portal.nav.menu')"
            >
                <nav class="site-header__panel-nav" aria-label="Primary">
                    <a
                        :href="section('platforms')"
                        :class="{ 'is-active': isSectionActive('platforms') }"
                        @click="closeMenu"
                    >{{ t('landing.nav.platforms') }}</a>
                    <a
                        :href="section('ai-connect')"
                        :class="{ 'is-active': isSectionActive('ai-connect') }"
                        @click="closeMenu"
                    >{{ t('landing.nav.ai_connect') }}</a>
                    <NuxtLink
                        to="/templates"
                        :class="{ 'is-active': isTemplatesActive }"
                        @click="closeMenu"
                    >{{ t('landing.nav.templates') }}</NuxtLink>
                    <a
                        :href="section('mockup')"
                        :class="{ 'is-active': isSectionActive('mockup') }"
                        @click="closeMenu"
                    >{{ t('landing.nav.mockup') }}</a>
                    <a
                        :href="section('pricing')"
                        :class="{ 'is-active': isSectionActive('pricing') }"
                        @click="closeMenu"
                    >{{ t('landing.nav.pricing') }}</a>
                    <a
                        :href="section('download')"
                        :class="{ 'is-active': isSectionActive('download') }"
                        @click="closeMenu"
                    >{{ t('landing.nav.download') }}</a>
                </nav>

                <div class="site-header__panel-actions">
                    <NuxtLink
                        v-if="user"
                        to="/portal"
                        class="site-header__panel-link"
                        @click="closeMenu"
                    >
                        {{ t('landing.nav.dashboard') }}
                    </NuxtLink>
                    <NuxtLink
                        v-else
                        to="/auth/login"
                        class="site-header__panel-link"
                        @click="closeMenu"
                    >
                        {{ t('landing.nav.login') }}
                    </NuxtLink>
                    <a
                        :href="playStore"
                        class="site-header__panel-cta"
                        target="_blank"
                        rel="noopener"
                        @click="trackDownload(); closeMenu()"
                    >
                        {{ t('landing.nav.cta') }}
                    </a>
                </div>
            </div>
        </div>

        <button
            v-if="menuOpen"
            type="button"
            class="site-header__backdrop"
            tabindex="-1"
            :aria-label="t('portal.nav.close_menu')"
            @click="closeMenu"
        />
    </header>
</template>
