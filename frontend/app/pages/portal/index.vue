<script setup lang="ts">
/**
 * /portal — dashboard.
 * Layout: welcome + stats + recent activity | quick actions rail.
 */
definePageMeta({ middleware: 'auth', layout: 'portal' });

const { t, locale } = useI18n();
const config = useRuntimeConfig();
const laravel = (config.public.laravelUrl as string).replace(/\/+$/, '');
const { user } = useAuthSession();
const portal = usePortalApi();
const api = useApi();
const { profile, refresh: refreshProfile, setProfile } = usePortalPublicProfile();
const { stats, refresh: refreshStats } = usePortalStats();

const cvs = ref<CVSummary[]>([]);
const letters = ref<CoverLetterSummary[]>([]);
const inbox = ref<InboxMessage[]>([]);
const loading = ref(true);
const createOpen = ref(false);
const createRoot = ref<HTMLElement | null>(null);
const creatingCv = ref(false);
const creatingProfile = ref(false);
const filter = ref<'all' | 'cvs' | 'letters' | 'inbox'>('all');

const topAtsDisplay = computed(() => {
    const score = stats.value?.top_ats_score;
    return score == null ? '—' : String(score);
});

async function loadDashboard() {
    loading.value = true;
    try {
        const [c, l, , inboxRes] = await Promise.all([
            portal.list<CVSummary>('/cvs'),
            portal.list<CoverLetterSummary>('/cover-letters'),
            refreshProfile(),
            api<{ result?: InboxMessage[]; data?: InboxMessage[] }>('/public-profiles/inbox').catch(() => null),
            refreshStats(),
        ]);
        cvs.value = c;
        letters.value = l;
        inbox.value = inboxRes?.result ?? inboxRes?.data ?? [];
    } finally {
        loading.value = false;
    }
}

onMounted(() => {
    loadDashboard();
});

// Re-fetch when navigating back to the dashboard from another portal page.
const route = useRoute();
watch(
    () => route.fullPath,
    (path, prev) => {
        if (path === '/portal' && prev && prev !== path) {
            loadDashboard();
        }
    },
);

const firstName = computed(() => {
    const u = user.value;
    if (!u) return '';
    const first = String(u.first_name || '').trim();
    if (first) return first;
    const fromName = String(u.name || '').trim().split(/\s+/).filter(Boolean)[0];
    if (fromName) return fromName;
    const email = String(u.email || '').trim();
    return email.includes('@') ? email.split('@')[0]! : email;
});

/** Greeting with a Laravel `:name` fallback if a locale wasn't converted. */
const welcomeTitle = computed(() => {
    const name = firstName.value;
    const raw = t('portal.dashboard.title', { name });
    if (!name) return raw.replace(/\s*[:{]name[}]?\s*/gi, '').replace(/,\s*$/, '').trim() || raw;
    return raw.replace(/\{name\}/g, name).replace(/:name\b/g, name);
});
const unreadCount = computed(() => inbox.value.filter((m) => !m.is_read).length);

type RecentItem = {
    id: string;
    kind: 'cv' | 'letter' | 'inbox';
    title: string;
    meta: string;
    tag: string;
    status?: string;
    statusTone?: 'success' | 'warning' | 'muted';
    href: string;
    at: number;
    icon: string;
};

function relativeTime(iso?: string) {
    if (!iso) return '—';
    const diff = Date.now() - new Date(iso).getTime();
    const mins = Math.round(diff / 60000);
    if (mins < 1) return t('portal.dashboard.time_just_now');
    if (mins < 60) return t('portal.dashboard.time_minutes', { n: mins });
    const hours = Math.round(mins / 60);
    if (hours < 24) return t('portal.dashboard.time_hours', { n: hours });
    const days = Math.round(hours / 24);
    if (days < 14) return t('portal.dashboard.time_days', { n: days });
    try {
        return new Date(iso).toLocaleDateString(locale.value);
    } catch {
        return new Date(iso).toLocaleDateString();
    }
}

const recentItems = computed<RecentItem[]>(() => {
    const items: RecentItem[] = [
        ...cvs.value.map((cv) => ({
            id: `cv-${cv.id}`,
            kind: 'cv' as const,
            title: cv.name,
            meta: t('portal.dashboard.updated_ago', { time: relativeTime(cv.updated_at) }),
            tag: t('portal.nav.cvs'),
            status: cv.is_public ? t('portal.dashboard.stat_published') : undefined,
            statusTone: cv.is_public ? 'success' as const : undefined,
            href: `/portal/cvs/${cv.id}/edit`,
            at: new Date(cv.updated_at || 0).getTime(),
            icon: 'file',
        })),
        ...letters.value.map((letter) => ({
            id: `letter-${letter.id}`,
            kind: 'letter' as const,
            title: letter.name,
            meta: [letter.company, relativeTime(letter.updated_at)].filter(Boolean).join(' · '),
            tag: t('portal.nav.cover_letters'),
            href: `/portal/cover-letters/${letter.id}/edit`,
            at: new Date(letter.updated_at || 0).getTime(),
            icon: 'mail',
        })),
        ...inbox.value.slice(0, 5).map((msg) => ({
            id: `inbox-${msg.id}`,
            kind: 'inbox' as const,
            title: msg.subject || msg.name || t('portal.inbox.title'),
            meta: relativeTime(msg.created_at),
            tag: t('portal.nav.inbox'),
            status: msg.is_read ? undefined : t('portal.inbox.unread'),
            statusTone: msg.is_read ? undefined : 'warning' as const,
            href: '/portal/inbox',
            at: new Date(msg.created_at || 0).getTime(),
            icon: 'inbox',
        })),
    ];
    return items.sort((a, b) => b.at - a.at).slice(0, 8);
});

const filteredRecent = computed(() => {
    if (filter.value === 'cvs') return recentItems.value.filter((i) => i.kind === 'cv');
    if (filter.value === 'letters') return recentItems.value.filter((i) => i.kind === 'letter');
    if (filter.value === 'inbox') return recentItems.value.filter((i) => i.kind === 'inbox');
    return recentItems.value;
});

const profileStatus = computed(() => {
    if (!profile.value?.id) return { label: t('portal.dashboard.stat_none'), tone: 'muted' as const };
    if (profile.value.is_public) return { label: t('portal.dashboard.stat_published'), tone: 'success' as const };
    return { label: t('portal.dashboard.stat_draft'), tone: 'warning' as const };
});

const profileUrl = computed(() => {
    if (profile.value?.public_url) return profile.value.public_url;
    if (!profile.value?.slug) return null;
    return `${laravel}/u/${profile.value.slug}`;
});

function onDocClick(e: MouseEvent) {
    if (createRoot.value && !createRoot.value.contains(e.target as Node)) createOpen.value = false;
}
onMounted(() => document.addEventListener('click', onDocClick));
onBeforeUnmount(() => document.removeEventListener('click', onDocClick));

async function startNewCv() {
    if (creatingCv.value) return;
    creatingCv.value = true;
    createOpen.value = false;
    try {
        const created = await portal.createBlankCv();
        if (created?.id) await navigateTo(`/portal/cvs/${created.id}/edit`);
    } finally {
        creatingCv.value = false;
    }
}

async function startPublicProfile() {
    if (creatingProfile.value) return;
    createOpen.value = false;
    if (profile.value?.id) {
        await navigateTo('/portal/public-profile');
        return;
    }
    creatingProfile.value = true;
    try {
        const created = await portal.createBlankPublicProfile();
        if (created?.id) {
            setProfile(created);
            await navigateTo('/portal/public-profile');
            return;
        }
        const existing = await refreshProfile();
        if (existing?.id) {
            await navigateTo('/portal/public-profile');
        }
    } finally {
        creatingProfile.value = false;
    }
}
</script>

<template>
    <header class="page-header">
        <div>
            <h1 class="page-header__title">{{ welcomeTitle }}</h1>
            <p class="page-header__subtitle">{{ t('portal.dashboard.subtitle') }}</p>
        </div>
        <div class="page-header__actions">
            <div ref="createRoot" class="create-menu">
                <button
                    type="button"
                    class="btn btn--primary"
                    :aria-expanded="createOpen"
                    aria-haspopup="menu"
                    @click="createOpen = !createOpen"
                >
                    <Icon name="plus" :size="15" />
                    {{ t('portal.dashboard.create_new') }}
                </button>
                <div class="create-menu__panel" :class="{ open: createOpen }" role="menu">
                    <button type="button" class="create-menu__item" role="menuitem" :disabled="creatingCv" @click="startNewCv">
                        <Icon name="file-plus" :size="15" />
                        <span>{{ creatingCv ? t('portal.cvs.creating') : t('portal.dashboard.new_cv') }}</span>
                    </button>
                    <NuxtLink to="/portal/cover-letters/create" class="create-menu__item" role="menuitem" @click="createOpen = false">
                        <Icon name="mail" :size="15" />
                        <span>{{ t('portal.dashboard.new_cover_letter') }}</span>
                    </NuxtLink>
                    <button type="button" class="create-menu__item" role="menuitem" :disabled="creatingProfile" @click="startPublicProfile">
                        <Icon name="user" :size="15" />
                        <span>{{ creatingProfile ? t('portal.common.loading') : t('portal.dashboard.create_public_profile') }}</span>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <DashboardSkeleton v-if="loading" />

    <template v-else>
    <div class="stats-grid">
        <NuxtLink to="/portal/cvs" class="stat">
            <div class="stat__top">
                <span class="stat__label">{{ t('portal.dashboard.stat_top_ats') }}</span>
                <span class="stat__icon"><Icon name="target" :size="16" /></span>
            </div>
            <span class="stat__value">{{ topAtsDisplay }}</span>
            <span class="stat__hint">
                {{ stats?.top_ats_score != null ? t('portal.dashboard.stat_top_ats_hint') : t('portal.dashboard.stat_top_ats_none') }}
            </span>
        </NuxtLink>
        <NuxtLink to="/portal/public-profile" class="stat">
            <div class="stat__top">
                <span class="stat__label">{{ t('portal.dashboard.stat_views') }}</span>
                <span class="stat__icon"><Icon name="eye" :size="16" /></span>
            </div>
            <span class="stat__value">{{ stats?.views_count ?? 0 }}</span>
            <span class="stat__hint">{{ t('portal.dashboard.stat_views_hint') }}</span>
        </NuxtLink>
        <NuxtLink to="/portal/inbox" class="stat">
            <div class="stat__top">
                <span class="stat__label">{{ t('portal.dashboard.stat_unread') }}</span>
                <span class="stat__icon"><Icon name="inbox" :size="16" /></span>
            </div>
            <span class="stat__value">{{ unreadCount }}</span>
            <span class="stat__hint">{{ t('portal.dashboard.stat_view_inbox') }}</span>
        </NuxtLink>
        <NuxtLink to="/portal/public-profile" class="stat">
            <div class="stat__top">
                <span class="stat__label">{{ t('portal.dashboard.stat_profile') }}</span>
                <span class="stat__icon"><Icon name="globe" :size="16" /></span>
            </div>
            <span class="stat__value stat__value--tag">
                <Tag :variant="profileStatus.tone === 'success' ? 'success' : profileStatus.tone === 'warning' ? 'warning' : 'soft'">
                    {{ profileStatus.label }}
                </Tag>
            </span>
            <span class="stat__hint">
                {{ profile?.id ? t('portal.dashboard.stat_edit') : t('portal.dashboard.stat_create') }}
            </span>
        </NuxtLink>
    </div>

    <div class="dash-layout">
        <article class="surface dash-card dash-card--recent">
            <header class="dash-card__head">
                <div>
                    <h2 class="dash-card__title">{{ t('portal.dashboard.recent_items') }}</h2>
                    <p class="dash-card__sub">{{ t('portal.dashboard.recent_activity_subtitle') }}</p>
                </div>
                <NuxtLink to="/portal/cvs" class="btn btn--ghost btn--sm">{{ t('portal.dashboard.view_all') }}</NuxtLink>
            </header>

            <div class="filter-pill dash-filters">
                <button type="button" :class="['filter-pill__btn', filter === 'all' && 'active']" @click="filter = 'all'">
                    {{ t('portal.dashboard.filter_all') }}
                </button>
                <button type="button" :class="['filter-pill__btn', filter === 'cvs' && 'active']" @click="filter = 'cvs'">
                    {{ t('portal.nav.cvs') }}
                </button>
                <button type="button" :class="['filter-pill__btn', filter === 'letters' && 'active']" @click="filter = 'letters'">
                    {{ t('portal.nav.cover_letters') }}
                </button>
                <button type="button" :class="['filter-pill__btn', filter === 'inbox' && 'active']" @click="filter = 'inbox'">
                    {{ t('portal.nav.inbox') }}
                </button>
            </div>

            <div v-if="filteredRecent.length === 0" class="empty empty--soft">
                <span class="empty__icon"><Icon name="files" :size="22" /></span>
                <p class="empty__title">{{ t('portal.dashboard.recent_empty') }}</p>
                <button type="button" class="btn btn--primary btn--sm" :disabled="creatingCv" @click="startNewCv">
                    {{ creatingCv ? t('portal.cvs.creating') : t('portal.dashboard.create_cv') }}
                </button>
            </div>

            <ul v-else class="recent-list">
                <li v-for="item in filteredRecent" :key="item.id">
                    <NuxtLink :to="item.href" class="recent-row">
                        <span class="list-card__icon"><Icon :name="item.icon" :size="17" /></span>
                        <div class="list-card__body">
                            <p class="list-card__title">{{ item.title }}</p>
                            <p class="list-card__meta">{{ item.meta }}</p>
                        </div>
                        <span class="recent-row__tag">{{ item.tag }}</span>
                        <span
                            v-if="item.status"
                            class="recent-row__status"
                            :class="`recent-row__status--${item.statusTone || 'muted'}`"
                        >
                            {{ item.status }}
                        </span>
                        <Icon name="chevron-right" :size="16" class="recent-row__chevron" />
                    </NuxtLink>
                </li>
            </ul>
        </article>

        <aside class="dash-rail">
            <article class="surface dash-widget">
                <h3 class="dash-widget__title">{{ t('portal.dashboard.profile_overview') }}</h3>
                <p class="dash-widget__text">
                    {{ profile?.headline || t('portal.dashboard.profile_overview_text') }}
                </p>
                <div class="dash-widget__meta">
                    <Tag :variant="profileStatus.tone === 'success' ? 'success' : profileStatus.tone === 'warning' ? 'warning' : 'soft'">
                        {{ profileStatus.label }}
                    </Tag>
                    <span v-if="profile?.slug" class="dash-widget__slug">/u/{{ profile.slug }}</span>
                </div>
                <a
                    v-if="profileUrl && profile?.is_public"
                    :href="profileUrl"
                    target="_blank"
                    rel="noopener"
                    class="btn btn--secondary btn--block"
                >
                    {{ t('portal.dashboard.view_profile') }}
                    <Icon name="external" :size="14" />
                </a>
                <NuxtLink v-else-if="profile?.id" to="/portal/public-profile" class="btn btn--secondary btn--block">
                    {{ t('portal.dashboard.stat_edit') }}
                </NuxtLink>
                <button v-else type="button" class="btn btn--secondary btn--block" :disabled="creatingProfile" @click="startPublicProfile">
                    {{ creatingProfile ? t('portal.common.loading') : t('portal.dashboard.stat_create') }}
                </button>
            </article>

            <article class="surface dash-widget">
                <h3 class="dash-widget__title">{{ t('portal.dashboard.quick_actions') }}</h3>
                <div class="quick-actions">
                    <button type="button" class="quick-action" :disabled="creatingCv" @click="startNewCv">
                        <span class="quick-action__icon"><Icon name="file-plus" :size="15" /></span>
                        <span>{{ creatingCv ? t('portal.cvs.creating') : t('portal.dashboard.new_cv') }}</span>
                        <Icon name="chevron-right" :size="15" />
                    </button>
                    <NuxtLink to="/portal/cover-letters/create" class="quick-action">
                        <span class="quick-action__icon"><Icon name="mail" :size="15" /></span>
                        <span>{{ t('portal.dashboard.new_cover_letter') }}</span>
                        <Icon name="chevron-right" :size="15" />
                    </NuxtLink>
                    <NuxtLink to="/portal/cvs" class="quick-action">
                        <span class="quick-action__icon"><Icon name="target" :size="15" /></span>
                        <span>{{ t('portal.dashboard.action_ats') }}</span>
                        <Icon name="chevron-right" :size="15" />
                    </NuxtLink>
                    <NuxtLink to="/portal/inbox" class="quick-action">
                        <span class="quick-action__icon"><Icon name="inbox" :size="15" /></span>
                        <span>{{ t('portal.dashboard.stat_view_inbox') }}</span>
                        <Icon name="chevron-right" :size="15" />
                    </NuxtLink>
                </div>
            </article>

            <article class="surface dash-tip dash-tip--light">
                <span class="dash-tip__icon"><Icon name="sparkles" :size="16" /></span>
                <div>
                    <h3 class="dash-tip__title">{{ t('portal.dashboard.tip_title') }}</h3>
                    <p class="dash-tip__text">{{ t('portal.dashboard.tip_text') }}</p>
                    <NuxtLink to="/portal/cvs" class="dash-tip__link">
                        {{ t('portal.dashboard.tip_cta') }}
                        <Icon name="arrow-right" :size="14" />
                    </NuxtLink>
                </div>
            </article>
        </aside>
    </div>
    </template>
</template>
