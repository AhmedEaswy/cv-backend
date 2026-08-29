<script setup lang="ts">
/**
 * /portal — dashboard.
 */
definePageMeta({ middleware: 'auth', layout: 'portal' });

const { t } = useI18n();
const config = useRuntimeConfig();
const laravel = (config.public.laravelUrl as string).replace(/\/+$/, '');
const { user } = useAuthSession();
const portal = usePortalApi();

const cvs = ref<CVSummary[]>([]);
const letters = ref<CoverLetterSummary[]>([]);
const profile = ref<ProfileData | null>(null);
const unreadCount = ref(0);

onMounted(async () => {
    const [c, l, p, i] = await Promise.all([
        portal.list<CVSummary>('/cvs'),
        portal.list<CoverLetterSummary>('/cover-letters'),
        portal.show<ProfileData>('/public-profiles'),
        portal.list<InboxMessage>('/public-profiles/inbox').catch(() => [] as InboxMessage[]),
    ]);
    cvs.value = c;
    letters.value = l;
    profile.value = p;
    // Inbox endpoint differs — we don't have a direct one for inbox. Pull count from public-profile if available.
    // Simpler: just call the inbox page endpoint if exposed via API. For now we read the latest CV letter.
    unreadCount.value = 0;
});

const firstName = computed(() => (user.value?.name || '').split(' ')[0] || user.value?.email || '');
const latestCv = computed(() => cvs.value[0] || null);
const latestLetter = computed(() => letters.value[0] || null);
</script>

<template>
    <header class="page-header">
        <div>
            <div class="page-header__eyebrow">{{ t('portal.dashboard.eyebrow') }}</div>
            <h1 class="page-header__title">{{ t('portal.dashboard.title', { name: firstName }) }}</h1>
            <p class="page-header__subtitle">{{ t('portal.dashboard.subtitle') }}</p>
        </div>
        <div class="page-header__actions">
            <NuxtLink to="/portal/cvs/create" class="btn btn--primary">
                <Icon name="file-plus" :size="15" />
                {{ t('portal.dashboard.new_cv') }}
            </NuxtLink>
            <NuxtLink to="/portal/cover-letters/create" class="btn btn--secondary">
                <Icon name="plus" :size="15" />
                {{ t('portal.dashboard.new_cover_letter') }}
            </NuxtLink>
        </div>
    </header>

    <div class="stats-grid">
        <div class="stat">
            <span class="stat__label">{{ t('portal.dashboard.stat_cvs') }}</span>
            <span class="stat__value">{{ cvs.length }}</span>
            <NuxtLink to="/portal/cvs" class="stat__hint">{{ t('portal.dashboard.stat_manage') }} →</NuxtLink>
        </div>
        <div class="stat">
            <span class="stat__label">{{ t('portal.dashboard.stat_cover_letters') }}</span>
            <span class="stat__value">{{ letters.length }}</span>
            <NuxtLink to="/portal/cover-letters" class="stat__hint">{{ t('portal.dashboard.stat_manage') }} →</NuxtLink>
        </div>
        <div class="stat">
            <span class="stat__label">{{ t('portal.dashboard.stat_unread') }}</span>
            <span class="stat__value">{{ unreadCount }}</span>
            <NuxtLink to="/portal/inbox" class="stat__hint">{{ t('portal.dashboard.stat_view_inbox') }} →</NuxtLink>
        </div>
        <div class="stat">
            <span class="stat__label">{{ t('portal.dashboard.stat_profile') }}</span>
            <span class="stat__value">
                <Tag v-if="profile" :variant="profile.is_published ? 'success' : 'warning'">
                    {{ profile.is_published ? t('portal.dashboard.stat_published') : t('portal.dashboard.stat_draft') }}
                </Tag>
                <Tag v-else variant="soft">{{ t('portal.dashboard.stat_none') }}</Tag>
            </span>
            <NuxtLink to="/portal/public-profile" class="stat__hint">
                {{ profile ? t('portal.dashboard.stat_edit') : t('portal.dashboard.stat_create') }} →
            </NuxtLink>
        </div>
    </div>

    <div class="dash-grid">
        <article class="surface dash-card">
            <header class="dash-card__head">
                <h2 class="dash-card__title">{{ t('portal.dashboard.recent_cvs') }}</h2>
                <NuxtLink to="/portal/cvs" class="btn btn--ghost btn--sm">{{ t('portal.dashboard.view_all') }}</NuxtLink>
            </header>
            <div v-if="latestCv" class="dash-list-item">
                <span class="list-card__icon"><Icon name="file" :size="18" /></span>
                <div class="list-card__body">
                    <p class="list-card__title">{{ latestCv.name }}</p>
                    <p class="list-card__meta">{{ t('portal.cvs.col.updated') }} · {{ new Date(latestCv.updated_at || Date.now()).toLocaleDateString() }}</p>
                </div>
                <NuxtLink :to="`/portal/cvs/${latestCv.id}/edit`" class="btn btn--secondary btn--sm">{{ t('portal.dashboard.open') }}</NuxtLink>
            </div>
            <div v-else class="empty">
                <span class="empty__icon"><Icon name="file-plus" :size="22" /></span>
                <p class="empty__title">{{ t('portal.dashboard.recent_cvs_empty') }}</p>
                <NuxtLink to="/portal/cvs/create" class="btn btn--primary btn--sm">{{ t('portal.dashboard.create_cv') }}</NuxtLink>
            </div>
        </article>

        <article class="surface dash-card">
            <header class="dash-card__head">
                <h2 class="dash-card__title">{{ t('portal.dashboard.recent_cover_letters') }}</h2>
                <NuxtLink to="/portal/cover-letters" class="btn btn--ghost btn--sm">{{ t('portal.dashboard.view_all') }}</NuxtLink>
            </header>
            <div v-if="latestLetter" class="dash-list-item">
                <span class="list-card__icon"><Icon name="mail" :size="18" /></span>
                <div class="list-card__body">
                    <p class="list-card__title">{{ latestLetter.name }}</p>
                    <p class="list-card__meta">{{ latestLetter.company || '—' }} · {{ new Date(latestLetter.updated_at || Date.now()).toLocaleDateString() }}</p>
                </div>
                <NuxtLink :to="`/portal/cover-letters/${latestLetter.id}/edit`" class="btn btn--secondary btn--sm">{{ t('portal.dashboard.open') }}</NuxtLink>
            </div>
            <div v-else class="empty">
                <span class="empty__icon"><Icon name="mail" :size="22" /></span>
                <p class="empty__title">{{ t('portal.dashboard.recent_cover_letters_empty') }}</p>
                <NuxtLink to="/portal/cover-letters/create" class="btn btn--primary btn--sm">{{ t('portal.dashboard.create_cover_letter') }}</NuxtLink>
            </div>
        </article>
    </div>

    <article class="surface dash-tip">
        <span class="dash-tip__icon"><Icon name="sparkles" :size="18" /></span>
        <div>
            <h3 class="dash-tip__title">{{ t('portal.dashboard.tip_title') }}</h3>
            <p class="dash-tip__text">{{ t('portal.dashboard.tip_text') }}</p>
        </div>
    </article>
</template>

<style scoped>
.stats-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1rem;
    margin-bottom: 1.5rem;
}
@media (min-width: 640px)  { .stats-grid { grid-template-columns: 1fr 1fr; } }
@media (min-width: 1024px) { .stats-grid { grid-template-columns: repeat(4, 1fr); } }
.stats-grid .stat__value { display: inline-flex; align-items: center; min-height: 2.5rem; }

.dash-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1rem;
    margin-bottom: 1.5rem;
}
@media (min-width: 1024px) { .dash-grid { grid-template-columns: 1fr 1fr; } }

.dash-card { padding: 1.5rem; }
.dash-card__head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; }
.dash-card__title { font-family: var(--font-display); font-size: 1.35rem; font-weight: 400; margin: 0; letter-spacing: -0.02em; }

.dash-list-item {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    padding: 0.85rem;
    background: var(--color-paper-2);
    border-radius: var(--radius-md);
    border: 1px solid var(--color-line);
}
.dash-tip {
    display: flex;
    align-items: flex-start;
    gap: 0.85rem;
    padding: 1.25rem 1.5rem;
    background: var(--color-ink);
    color: var(--color-paper);
    border: 1px solid var(--color-ink);
    border-radius: var(--radius-lg);
}
.dash-tip__icon {
    width: 36px;
    height: 36px;
    border-radius: var(--radius-md);
    background: rgba(255,255,255,0.12);
    color: var(--color-paper);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.dash-tip__title { font-size: 0.9rem; font-weight: 600; margin: 0 0 0.2rem; }
.dash-tip__text { font-size: 0.85rem; margin: 0; color: rgba(250,250,249,0.78); line-height: 1.5; }
</style>
