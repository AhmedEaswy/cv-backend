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

interface SettingsNavItem {
    id: string;
    to: string;
    label: string;
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
    { to: '/portal/help', label: t('portal.nav.help'), icon: 'help', match: (p) => p.startsWith('/portal/help') },
    ...(stats.value?.inbox_enabled !== false
        ? [{ to: '/portal/inbox', label: t('portal.nav.inbox'), icon: 'inbox', match: (p: string) => p.startsWith('/portal/inbox') }]
        : []),
]);

const settingsNavItems = computed<SettingsNavItem[]>(() => [
    { id: 'profile', to: '/portal/settings', label: t('portal.settings.profile.title') },
    { id: 'password', to: '/portal/settings?tab=password', label: t('portal.settings.password.title') },
    { id: 'notifications', to: '/portal/settings/notifications', label: t('portal.settings.notifications.title') },
    { id: 'sending', to: '/portal/settings/sending-email', label: t('portal.settings.sending_email.smtp_title') },
    { id: 'ai', to: '/portal/settings/ai-access', label: t('portal.settings.ai.title') },
]);

const settingsOpen = ref(false);

const isOnSettingsRoute = computed(() => route.path.startsWith('/portal/settings'));

function isSettingsSubActive(item: SettingsNavItem): boolean {
    const path = route.path;
    const tab = route.query.tab;
    switch (item.id) {
        case 'profile':
            return path === '/portal/settings' && tab !== 'password';
        case 'password':
            return path === '/portal/settings' && tab === 'password';
        case 'notifications':
            return path.startsWith('/portal/settings/notifications');
        case 'sending':
            return path.startsWith('/portal/settings/sending-email');
        case 'ai':
            return path.startsWith('/portal/settings/ai-access');
        default:
            return false;
    }
}

const isSettingsSectionActive = computed(() =>
    settingsNavItems.value.some((item) => isSettingsSubActive(item)),
);

function syncSettingsOpen() {
    if (isOnSettingsRoute.value) {
        settingsOpen.value = true;
    }
}

function toggleSettingsMenu() {
    settingsOpen.value = !settingsOpen.value;
}

const initials = computed(() => {
    const n = user.value?.name || user.value?.email || '?';
    return n.split(' ').map((s: string) => s[0]).join('').slice(0, 2).toUpperCase();
});
const avatarUrl = useGravatar(() => user.value?.email, 84);

function isActive(item: NavItem) {
    return item.match ? item.match(route.path) : route.path.startsWith(item.to);
}

/** Off-canvas drawer only below tablet; ≥768px keeps the sidebar visible. */
const DRAWER_MQ = '(max-width: 767.98px)';
const sidebarOpen = ref(false);
const isDrawerViewport = ref(false);

function syncViewportMode() {
    if (!import.meta.client) return;
    isDrawerViewport.value = window.matchMedia(DRAWER_MQ).matches;
    if (!isDrawerViewport.value) {
        sidebarOpen.value = false;
        document.body.style.overflow = '';
    }
}

function setSidebarOpen(open: boolean) {
    sidebarOpen.value = open;
    if (import.meta.client && isDrawerViewport.value) {
        document.body.style.overflow = open ? 'hidden' : '';
    }
}

function toggleSidebar() {
    setSidebarOpen(!sidebarOpen.value);
}

function closeSidebar() {
    setSidebarOpen(false);
}

watch(() => route.fullPath, () => {
    closeSidebar();
    refreshStats();
    syncSettingsOpen();
});

function onKeydown(e: KeyboardEvent) {
    if (e.key === 'Escape') closeSidebar();
}

onMounted(() => {
    syncViewportMode();
    window.addEventListener('resize', syncViewportMode);
    document.addEventListener('keydown', onKeydown);
    refreshStats();
    syncSettingsOpen();
});

onBeforeUnmount(() => {
    window.removeEventListener('resize', syncViewportMode);
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
                    :aria-label="sidebarOpen ? t('portal.nav.close_menu') : t('portal.nav.menu')"
                    @click.stop="toggleSidebar"
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
                    <PortalNotificationBell />
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
            @click="closeSidebar"
        />

        <aside
            id="portal-sidebar"
            class="portal-sidebar no-print"
            :aria-hidden="isDrawerViewport && !sidebarOpen ? 'true' : undefined"
        >
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
                        :data-portal-tour="item.icon === 'dashboard' ? 'dashboard' : item.icon === 'file' ? 'cvs' : item.icon === 'mail' ? 'cover-letters' : item.icon === 'user' ? 'public-profile' : item.icon === 'help' ? 'help' : undefined"
                        @click="closeSidebar"
                    >
                        <Icon :name="item.icon" :size="16" />
                        <span class="portal-sidebar__link-label">{{ item.label }}</span>
                        <span v-if="item.count != null" class="portal-sidebar__count">{{ item.count }}</span>
                    </NuxtLink>
                </div>

                <div class="portal-sidebar__group portal-sidebar__group--secondary">
                    <div class="portal-sidebar__nav-section">
                        <button
                            type="button"
                            class="portal-sidebar__link portal-sidebar__link--parent"
                            :class="{ 'is-section-active': isSettingsSectionActive, 'is-open': settingsOpen }"
                            :aria-expanded="settingsOpen"
                            aria-controls="portal-settings-subnav"
                            @click="toggleSettingsMenu"
                        >
                            <Icon name="settings" :size="16" />
                            <span class="portal-sidebar__link-label">{{ t('portal.nav.settings') }}</span>
                            <Icon
                                name="chevron-down"
                                :size="16"
                                class="portal-sidebar__chevron"
                            />
                        </button>
                        <Collapse :open="settingsOpen">
                            <div
                                id="portal-settings-subnav"
                                class="portal-sidebar__subnav"
                                role="group"
                                :aria-label="t('portal.nav.settings')"
                            >
                                <NuxtLink
                                    v-for="item in settingsNavItems"
                                    :key="item.id"
                                    :to="item.to"
                                    class="portal-sidebar__link portal-sidebar__link--sub"
                                    :aria-current="isSettingsSubActive(item) ? 'page' : undefined"
                                    @click="closeSidebar"
                                >
                                    <span>{{ item.label }}</span>
                                </NuxtLink>
                            </div>
                        </Collapse>
                    </div>
                </div>
            </nav>

            <div class="portal-sidebar__foot">
                <NuxtLink to="/" class="portal-sidebar__link portal-sidebar__site" @click="closeSidebar">
                    <Icon name="arrow-left" :size="16" />
                    <span>{{ t('portal.nav.back_to_site') }}</span>
                </NuxtLink>
                <button type="button" class="portal-sidebar__link portal-sidebar__link--danger" @click="logout">
                    <Icon name="log-out" :size="16" />
                    <span>{{ t('portal.nav.signout') }}</span>
                </button>
            </div>
        </aside>

        <main class="portal-content">
            <slot />
        </main>

        <PortalTourHost />
    </div>
</template>
