<script setup lang="ts">
/**
 * /portal/cvs — list of CVs.
 */
definePageMeta({ middleware: 'auth', layout: 'portal' });
import type { CVSummary } from '~/composables/usePortalApi';

const { t } = useI18n();
const portal = usePortalApi();
const cvs = ref<CVSummary[]>([]);
const loading = ref(true);

onMounted(async () => {
    cvs.value = await portal.list<CVSummary>('/cvs');
    loading.value = false;
});

async function onDelete(cv: CVSummary) {
    if (!confirm(t('portal.cvs.delete_confirm'))) return;
    const ok = await portal.destroy(`/cvs/${cv.id}`, t('portal.cvs.deleted'));
    if (ok) cvs.value = cvs.value.filter((c) => c.id !== cv.id);
}
async function onDuplicate(cv: CVSummary) {
    const r = await portal.duplicate(`/cvs/${cv.id}/duplicate`);
    if (r?.id) cvs.value = [r, ...cvs.value];
}

function languageName(code?: string) {
    const map: Record<string, string> = {
        en: 'English', ar: 'العربية', tr: 'Türkçe', es: 'Español',
        fr: 'Français', de: 'Deutsch', ur: 'اردو',
    };
    return code ? (map[code] || code.toUpperCase()) : '—';
}
function timeAgo(iso?: string) {
    if (!iso) return '—';
    const d = new Date(iso);
    return d.toLocaleDateString();
}
</script>

<template>
    <header class="page-header">
        <div>
            <div class="page-header__eyebrow">{{ t('portal.cvs.eyebrow') }}</div>
            <h1 class="page-header__title">{{ t('portal.cvs.title') }}</h1>
            <p class="page-header__subtitle">{{ t('portal.cvs.subtitle') }}</p>
        </div>
        <div class="page-header__actions">
            <NuxtLink to="/portal/cvs/create" class="btn btn--primary">
                <Icon name="plus" :size="15" />
                {{ t('portal.cvs.new_cv') }}
            </NuxtLink>
        </div>
    </header>

    <div v-if="loading" class="empty">
        <span class="empty__icon"><Icon name="clock" :size="22" /></span>
        <p class="empty__title">{{ t('portal.common.loading') }}</p>
    </div>

    <div v-else-if="cvs.length === 0" class="empty">
        <span class="empty__icon"><Icon name="file-plus" :size="22" /></span>
        <p class="empty__title">{{ t('portal.cvs.empty') }}</p>
        <p class="empty__text">{{ t('portal.cvs.empty_text') }}</p>
        <NuxtLink to="/portal/cvs/create" class="btn btn--primary">{{ t('portal.cvs.create_first') }}</NuxtLink>
    </div>

    <div v-else class="list">
        <article v-for="cv in cvs" :key="cv.id" class="list-card">
            <span class="list-card__icon"><Icon name="file" :size="18" /></span>
            <div class="list-card__body">
                <p class="list-card__title">
                    <NuxtLink :to="`/portal/cvs/${cv.id}/edit`" class="list-card__link">{{ cv.name }}</NuxtLink>
                </p>
                <p class="list-card__meta">
                    {{ languageName(cv.language) }} · {{ t('portal.cvs.col.updated') }} {{ timeAgo(cv.updated_at) }}
                </p>
            </div>
            <div class="list-card__actions">
                <Tag v-if="cv.is_public" variant="success">{{ t('portal.cvs.status_published') }}</Tag>
                <Tag v-else variant="soft">{{ t('portal.cvs.status_draft') }}</Tag>
                <Button variant="ghost" size="sm" @click="onDuplicate(cv)" :aria-label="t('portal.cvs.duplicate')">
                    <Icon name="copy" :size="14" />
                </Button>
                <NuxtLink :to="`/portal/cvs/${cv.id}/edit`" class="btn btn--secondary btn--sm">
                    {{ t('portal.cvs.edit') }}
                </NuxtLink>
                <Button variant="danger" size="sm" @click="onDelete(cv)" :aria-label="t('portal.cvs.delete')">
                    <Icon name="trash" :size="14" />
                </Button>
            </div>
        </article>
    </div>
</template>

