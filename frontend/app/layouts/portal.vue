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

<style scoped>
.portal-topbar__right {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}
.portal-avatar { position: relative; }
.portal-avatar__btn {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: var(--color-ink);
    color: var(--color-paper);
    border: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.78rem;
    cursor: pointer;
    transition: transform 0.15s ease;
}
.portal-avatar__btn:hover { transform: scale(1.04); }
.portal-avatar__initials { line-height: 1; }
.portal-avatar__menu {
    position: absolute;
    top: calc(100% + 8px);
    inset-inline-end: 0;
    min-width: 14rem;
    background: var(--color-white);
    border: 1px solid var(--color-line);
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-3);
    padding: 0.35rem;
    z-index: 50;
    opacity: 0;
    transform: translateY(-4px);
    pointer-events: none;
    transition: opacity 0.12s ease, transform 0.12s ease;
}
.portal-avatar__menu.open { opacity: 1; transform: translateY(0); pointer-events: auto; }
.portal-avatar__head { padding: 0.65rem 0.75rem 0.5rem; border-bottom: 1px solid var(--color-line); margin-bottom: 0.35rem; }
.portal-avatar__name { font-weight: 600; color: var(--color-ink); font-size: 0.875rem; }
.portal-avatar__email { color: var(--color-muted); font-size: 0.78rem; margin-top: 0.1rem; }
.portal-avatar__item {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0.5rem 0.7rem;
    border-radius: var(--radius-sm);
    color: var(--color-ink);
    text-decoration: none;
    font-size: 0.875rem;
    background: transparent;
    border: 0;
    width: 100%;
    cursor: pointer;
    text-align: start;
    transition: background 0.1s ease;
    font-family: inherit;
}
.portal-avatar__item:hover { background: var(--color-paper-2); }
.portal-avatar__item--danger { color: var(--color-danger); }
.portal-avatar__item--danger:hover { background: var(--color-danger-soft); }
</style>
