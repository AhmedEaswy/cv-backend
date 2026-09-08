<script setup lang="ts">
/**
 * /portal/cover-letters — list.
 */
definePageMeta({ middleware: 'auth', layout: 'portal' });
import type { CoverLetterSummary } from '~/composables/usePortalApi';

const { t } = useI18n();
const portal = usePortalApi();
const { refresh: refreshStats } = usePortalStats();
const letters = ref<CoverLetterSummary[]>([]);
const loading = ref(true);

onMounted(async () => {
    letters.value = await portal.list<CoverLetterSummary>('/cover-letters');
    loading.value = false;
});

async function onDelete(l: CoverLetterSummary) {
    if (!confirm(t('portal.cover_letters.delete_confirm'))) return;
    const ok = await portal.destroy(`/cover-letters/${l.id}`, t('portal.cover_letters.deleted'));
    if (ok) {
        letters.value = letters.value.filter((x) => x.id !== l.id);
        refreshStats();
    }
}
async function onDuplicate(l: CoverLetterSummary) {
    const r = await portal.duplicate(`/cover-letters/${l.id}/duplicate`);
    if (r?.id) {
        letters.value = [r, ...letters.value];
        refreshStats();
    }
}

function timeAgo(iso?: string) {
    if (!iso) return '—';
    return new Date(iso).toLocaleDateString();
}
</script>

<template>
    <header class="page-header">
        <div>
            <div class="page-header__eyebrow">{{ t('portal.cover_letters.eyebrow') }}</div>
            <h1 class="page-header__title">{{ t('portal.cover_letters.title') }}</h1>
            <p class="page-header__subtitle">{{ t('portal.cover_letters.subtitle') }}</p>
        </div>
        <div class="page-header__actions">
            <NuxtLink to="/portal/cover-letters/create" class="btn btn--primary">
                <Icon name="plus" :size="15" /> {{ t('portal.cover_letters.new') }}
            </NuxtLink>
        </div>
    </header>

    <ListSkeleton v-if="loading" />

    <div v-else-if="letters.length === 0" class="empty">
        <span class="empty__icon"><Icon name="mail" :size="22" /></span>
        <p class="empty__title">{{ t('portal.cover_letters.empty') }}</p>
        <p class="empty__text">{{ t('portal.cover_letters.empty_text') }}</p>
        <NuxtLink to="/portal/cover-letters/create" class="btn btn--primary">{{ t('portal.cover_letters.create_first') }}</NuxtLink>
    </div>

    <div v-else class="list">
        <article v-for="l in letters" :key="l.id" class="list-card">
            <span class="list-card__icon"><Icon name="mail" :size="18" /></span>
            <div class="list-card__body">
                <p class="list-card__title">
                    <NuxtLink :to="`/portal/cover-letters/${l.id}/edit`" class="list-card__link">{{ l.name }}</NuxtLink>
                </p>
                <p class="list-card__meta">
                    {{ l.company || '—' }} · {{ t('portal.cvs.col.updated') }} {{ timeAgo(l.updated_at) }}
                </p>
            </div>
            <div class="list-card__actions">
                <Button variant="ghost" size="sm" icon @click="onDuplicate(l)" :aria-label="t('portal.common.duplicate')">
                    <Icon name="copy" :size="14" />
                </Button>
                <NuxtLink :to="`/portal/cover-letters/${l.id}/edit`" class="btn btn--secondary btn--sm">
                    {{ t('portal.common.edit') }}
                </NuxtLink>
                <Button variant="danger" size="sm" icon @click="onDelete(l)" :aria-label="t('portal.common.delete')">
                    <Icon name="trash" :size="14" />
                </Button>
            </div>
        </article>
    </div>
</template>

