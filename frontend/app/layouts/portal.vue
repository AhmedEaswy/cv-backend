<script setup lang="ts">
/**
 * <PortalLayout> — authed portal shell.
 *
 * Light workspace: sticky top bar + left sidebar nav + main content.
 * Mobile: hamburger opens an off-canvas sidebar drawer.
 */
const { t } = useI18n();
const config = useRuntimeConfig();
const appName = config.public.appName as string;
const laravel = (config.public.laravelUrl as string).replace(/\/+$/, '');
const logoSrc = `${laravel}/images/logo-horizontal.png`;
const route = useRoute();
const { user, logout } = useAuthSession();

interface NavItem {
    to: string;
    label: string;
    icon: string;
    count?: number | null;
    match?: (path: string) => boolean;
}

const { stats, refresh: refreshStats } = usePortalStats();

const primaryNav = computed<NavItem[]>(() => [
    { to: '/portal', label: t('portal.nav.dashboard'), icon: 'dashboard', match: (p) => p === '/portal' },
    {
        to: '/portal/cvs',
        label: t('portal.nav.cvs'),
        icon: 'file',
        count: stats.value?.cvs_count ?? null,
        match: (p) => p.startsWith('/portal/cvs'),
    },
    {
        to: '/portal/cover-letters',
        label: t('portal.nav.cover_letters'),
        icon: 'mail',
        count: stats.value?.cover_letters_count ?? null,
        match: (p) => p.startsWith('/portal/cover-letters'),
    },
    { to: '/portal/public-profile', label: t('portal.nav.public_profile'), icon: 'user', match: (p) => p.startsWith('/portal/public-profile') },
    { to: '/portal/inbox', label: t('portal.nav.inbox'), icon: 'inbox', match: (p) => p.startsWith('/portal/inbox') },
]);

const secondaryNav = computed<NavItem[]>(() => [
    { to: '/portal/settings', label: t('portal.nav.settings'), icon: 'settings', match: (p) => p.startsWith('/portal/settings') && !p.startsWith('/portal/settings/ai-access') },
    { to: '/portal/settings/ai-access', label: t('portal.nav.ai_access'), icon: 'sparkles', match: (p) => p.startsWith('/portal/settings/ai-access') },
]);

const initials = computed(() => {
    const n = user.value?.name || user.value?.email || '?';
    return n.split(' ').map((s: string) => s[0]).join('').slice(0, 2).toUpperCase();
});
const avatarUrl = useGravatar(() => user.value?.email, 84);

function isActive(item: NavItem) {
    return item.match ? item.match(route.path) : route.path.startsWith(item.to);
}

const sidebarOpen = ref(false);
watch(() => route.fullPath, () => {
    sidebarOpen.value = false;
    refreshStats();
});
watch(sidebarOpen, (open) => {
    if (import.meta.client) document.body.style.overflow = open ? 'hidden' : '';
});

function onKeydown(e: KeyboardEvent) {
    if (e.key === 'Escape') sidebarOpen.value = false;
}
onMounted(() => {
    document.addEventListener('keydown', onKeydown);
    refreshStats();
});
onBeforeUnmount(() => {
    document.removeEventListener('keydown', onKeydown);
    document.body.style.overflow = '';
});
</script>

<template>
    <div class="portal-shell" :class="{ 'portal-shell--nav-open': sidebarOpen }">
        <header class="portal-topbar no-print">
            <div class="portal-topbar__inner">
                <button
                    type="button"
                    class="portal-topbar__menu"
                    :aria-expanded="sidebarOpen"
                    aria-controls="portal-sidebar"
                    :aria-label="t('portal.nav.menu')"
                    @click="sidebarOpen = !sidebarOpen"
                >
                    <Icon :name="sidebarOpen ? 'close' : 'menu'" :size="18" />
                </button>

                <NuxtLink to="/portal" class="portal-brand" :aria-label="`${appName} — ${t('portal.nav.dashboard')}`">
                    <img
                        :src="logoSrc"
                        :alt="appName"
                        class="portal-brand__logo"
                        @error="(($event.target as HTMLImageElement).src = `${laravel}/images/logo-icon.png`)"
                    />
                </NuxtLink>

                <div class="portal-topbar__right">
                    <LangSwitcher />
                    <NuxtLink to="/" class="portal-topbar__site">
                        {{ t('portal.nav.back_to_site') }}
                    </NuxtLink>
                </div>
            </div>
        </header>

        <div
            class="portal-backdrop no-print"
            :class="{ open: sidebarOpen }"
            aria-hidden="true"
            @click="sidebarOpen = false"
        />

        <aside id="portal-sidebar" class="portal-sidebar no-print">
            <div class="portal-sidebar__profile">
                <span class="portal-sidebar__avatar" aria-hidden="true">
                    <img
                        v-if="avatarUrl"
                        :src="avatarUrl"
                        alt=""
                        class="portal-sidebar__avatar-img"
                        width="42"
                        height="42"
                        @error="(($event.target as HTMLImageElement).style.display = 'none')"
                    >
                    <span class="portal-sidebar__avatar-fallback">{{ initials }}</span>
                </span>
                <div class="portal-sidebar__identity">
                    <div class="portal-sidebar__name">{{ user?.name || '—' }}</div>
                    <div class="portal-sidebar__email">{{ user?.email }}</div>
                </div>
            </div>

            <nav class="portal-sidebar__nav" aria-label="Portal">
                <div class="portal-sidebar__group">
                    <NuxtLink
                        v-for="item in primaryNav"
                        :key="item.to"
                        :to="item.to"
                        class="portal-sidebar__link"
                        :aria-current="isActive(item) ? 'page' : undefined"
                    >
                        <Icon :name="item.icon" :size="16" />
                        <span class="portal-sidebar__link-label">{{ item.label }}</span>
                        <span v-if="item.count != null" class="portal-sidebar__count">{{ item.count }}</span>
                    </NuxtLink>
                </div>

                <div class="portal-sidebar__group portal-sidebar__group--secondary">
                    <NuxtLink
                        v-for="item in secondaryNav"
                        :key="item.to"
                        :to="item.to"
                        class="portal-sidebar__link"
                        :aria-current="isActive(item) ? 'page' : undefined"
                    >
                        <Icon :name="item.icon" :size="16" />
                        <span>{{ item.label }}</span>
                    </NuxtLink>
                </div>
            </nav>

            <div class="portal-sidebar__foot">
                <button type="button" class="portal-sidebar__link portal-sidebar__link--danger" @click="logout">
                    <Icon name="log-out" :size="16" />
                    <span>{{ t('portal.nav.signout') }}</span>
                </button>
            </div>
        </aside>

        <main class="portal-content">
            <slot />
        </main>
    </div>
</template>
