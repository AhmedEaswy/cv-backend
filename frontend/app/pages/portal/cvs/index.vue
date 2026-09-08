<script setup lang="ts">
/**
 * /portal/cvs — list of CVs.
 */
definePageMeta({ middleware: 'auth', layout: 'portal' });
import type { CVSummary } from '~/composables/usePortalApi';
import type { DropdownMenuItem } from '~/components/ui/DropdownMenu.vue';

const { t } = useI18n();
const route = useRoute();
const router = useRouter();
const portal = usePortalApi();
const { refresh: refreshStats } = usePortalStats();
const cvs = ref<CVSummary[]>([]);
const loading = ref(true);
const creating = ref(false);

async function openCreate() {
    if (creating.value) return;
    creating.value = true;
    try {
        const created = await portal.createBlankCv();
        if (created?.id) {
            refreshStats();
            await router.push(`/portal/cvs/${created.id}/edit`);
            return;
        }
    } finally {
        creating.value = false;
    }
}

onMounted(async () => {
    cvs.value = await portal.list<CVSummary>('/cvs');
    loading.value = false;
    if (route.query.create === '1') {
        router.replace({ path: '/portal/cvs', query: {} });
        await openCreate();
    }
});

watch(() => route.query.create, async (v) => {
    if (v === '1') {
        router.replace({ path: '/portal/cvs', query: {} });
        await openCreate();
    }
});

async function onDelete(cv: CVSummary) {
    if (!confirm(t('portal.cvs.delete_confirm'))) return;
    const ok = await portal.destroy(`/cvs/${cv.id}`, t('portal.cvs.deleted'));
    if (ok) {
        cvs.value = cvs.value.filter((c) => c.id !== cv.id);
        refreshStats();
    }
}

async function onDuplicate(cv: CVSummary) {
    const r = await portal.duplicate(`/cvs/${cv.id}/duplicate`);
    if (r?.id) {
        cvs.value = [r, ...cvs.value];
        refreshStats();
    }
}

function onEdit(cv: CVSummary) {
    return router.push(`/portal/cvs/${cv.id}/edit`);
}

function menuItems(cv: CVSummary): DropdownMenuItem[] {
    return [
        { key: 'edit', label: t('portal.cvs.edit'), icon: 'edit', to: `/portal/cvs/${cv.id}/edit` },
        { key: 'duplicate', label: t('portal.cvs.duplicate'), icon: 'copy' },
        { key: 'delete', label: t('portal.cvs.delete'), icon: 'trash', danger: true },
    ];
}

async function onAction(cv: CVSummary, key: string) {
    if (key === 'edit') return onEdit(cv);
    if (key === 'duplicate') return onDuplicate(cv);
    if (key === 'delete') return onDelete(cv);
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
            <button type="button" class="btn btn--primary" :disabled="creating" @click="openCreate">
                <Icon name="plus" :size="15" />
                {{ creating ? t('portal.cvs.creating') : t('portal.cvs.new_cv') }}
            </button>
        </div>
    </header>

    <ListSkeleton v-if="loading" />

    <div v-else-if="cvs.length === 0" class="empty">
        <span class="empty__icon"><Icon name="file-plus" :size="22" /></span>
        <p class="empty__title">{{ t('portal.cvs.empty') }}</p>
        <p class="empty__text">{{ t('portal.cvs.empty_text') }}</p>
        <button type="button" class="btn btn--primary" :disabled="creating" @click="openCreate">
            {{ creating ? t('portal.cvs.creating') : t('portal.cvs.create_first') }}
        </button>
    </div>

    <div v-else class="list">
        <article v-for="cv in cvs" :key="cv.id" class="list-card">
            <ScoreRing :score="cv.latest_ats_score" :grade="cv.latest_ats_grade" />
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
                <DropdownMenu
                    :items="menuItems(cv)"
                    :label="t('portal.cvs.actions')"
                    @select="(key) => onAction(cv, key)"
                />
            </div>
        </article>
    </div>
</template>
