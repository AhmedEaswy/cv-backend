<script setup lang="ts">
/**
 * <PortalLayout> — the authed portal shell.
 *
 * Top bar: brand + horizontal nav (Dashboard, CVs, Cover letters,
 * Public profile, Inbox, Settings) + lang switcher + avatar menu.
 *
 * Body:  <main class="portal-content"><slot/></main>
 *
 * No sidebar — the new design uses a horizontal nav in a top bar
 * for a more "product" feel. (Sidebars are an admin-panel pattern.)
 */
const { t } = useI18n();
const config = useRuntimeConfig();
const appName = config.public.appName as string;
const route = useRoute();
const { user, logout } = useAuthSession();

interface NavItem { to: string; label: string; icon: string; badge?: boolean; match?: (path: string) => boolean }
const nav: NavItem[] = [
    { to: '/portal',              label: t('portal.nav.dashboard'),     icon: 'dashboard',       match: (p) => p === '/portal' },
    { to: '/portal/cvs',          label: t('portal.nav.cvs'),           icon: 'file',            match: (p) => p.startsWith('/portal/cvs') },
    { to: '/portal/cover-letters', label: t('portal.nav.cover_letters'), icon: 'mail',           match: (p) => p.startsWith('/portal/cover-letters') },
    { to: '/portal/public-profile', label: t('portal.nav.public_profile'), icon: 'user',         match: (p) => p.startsWith('/portal/public-profile') },
    { to: '/portal/inbox',        label: t('portal.nav.inbox'),         icon: 'inbox',           match: (p) => p.startsWith('/portal/inbox') },
    { to: '/portal/settings',     label: t('portal.nav.settings'),      icon: 'settings',        match: (p) => p.startsWith('/portal/settings') },
];

const initials = computed(() => {
    const n = user.value?.name || user.value?.email || '?';
    return n.split(' ').map((s: string) => s[0]).join('').slice(0, 2).toUpperCase();
});

const menuOpen = ref(false);
const menuRoot = ref<HTMLElement | null>(null);
function closeMenu() { menuOpen.value = false; }
function toggleMenu() { menuOpen.value = !menuOpen.value; }

function onDocClick(e: MouseEvent) {
    if (menuRoot.value && !menuRoot.value.contains(e.target as Node)) closeMenu();
}
onMounted(() => document.addEventListener('click', onDocClick));
onBeforeUnmount(() => document.removeEventListener('click', onDocClick));
</script>

<template>
    <div class="portal-shell">
        <header class="portal-topbar no-print">
            <div class="portal-topbar__inner">
                <NuxtLink to="/portal" class="portal-brand">
                    <span class="portal-brand__mark" aria-hidden="true">S</span>
                    <span>{{ appName }}</span>
                </NuxtLink>

                <nav class="portal-nav" aria-label="Portal">
                    <NuxtLink
                        v-for="item in nav"
                        :key="item.to"
                        :to="item.to"
                        :aria-current="(item.match ? item.match(route.path) : route.path.startsWith(item.to)) ? 'page' : undefined"
                    >
                        <Icon :name="item.icon" :size="15" />
                        <span>{{ item.label }}</span>
                        <span v-if="item.badge" class="portal-nav__badge" />
                    </NuxtLink>
                </nav>

                <div class="portal-topbar__right">
                    <LangSwitcher />
                    <div ref="menuRoot" class="portal-avatar">
                        <button
                            type="button"
                            class="portal-avatar__btn"
                            :aria-expanded="menuOpen"
                            aria-haspopup="menu"
                            @click="toggleMenu"
                        >
                            <span class="portal-avatar__initials">{{ initials }}</span>
                        </button>
                        <div class="portal-avatar__menu" :class="{ open: menuOpen }" role="menu">
                            <div class="portal-avatar__head">
                                <div class="portal-avatar__name">{{ user?.name }}</div>
                                <div class="portal-avatar__email">{{ user?.email }}</div>
                            </div>
                            <NuxtLink to="/portal/settings" class="portal-avatar__item" role="menuitem" @click="closeMenu">
                                <Icon name="settings" :size="15" />
                                <span>{{ t('portal.nav.settings') }}</span>
                            </NuxtLink>
                            <button type="button" class="portal-avatar__item portal-avatar__item--danger" role="menuitem" @click="logout">
                                <Icon name="log-out" :size="15" />
                                <span>{{ t('portal.nav.signout') }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="portal-content">
            <slot />
        </main>
    </div>
</template>

