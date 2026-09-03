<script setup lang="ts">
/**
 * /portal/cvs/[id]/edit — edit a single CV. Opens the ATS modal in
 * a side panel from the top bar; the modal itself lives in
 * components/portal/AtsModal.vue.
 */
import type { CVSummary, ProfileData } from '~/composables/usePortalApi';

definePageMeta({ middleware: 'auth', layout: 'portal' });

const { t } = useI18n();
const route = useRoute();
const router = useRouter();
const portal = usePortalApi();

const id = computed(() => Number(route.params.id));
const cv = ref<CVSummary | null>(null);
const profile = ref<ProfileData | null>(null);
const templates = ref<Array<{ id: number; name: string }>>([]);
const loading = ref(true);
const saving = ref(false);
const atsOpen = ref(false);

const langs = [
    { code: 'en', name: 'English' },
    { code: 'ar', name: 'العربية' },
    { code: 'tr', name: 'Türkçe' },
    { code: 'es', name: 'Español' },
    { code: 'fr', name: 'Français' },
    { code: 'de', name: 'Deutsch' },
    { code: 'ur', name: 'اردو' },
];

const form = reactive({
    name: '',
    language: 'en',
    template_id: '' as string | number,
    is_public: false,
});

onMounted(async () => {
    const [c, p, t1, t2] = await Promise.all([
        portal.show<CVSummary>(`/cvs/${id.value}`),
        portal.show<ProfileData>('/public-profiles'),
        portal.list<any>('/cvs/templates').catch(() => []),
        portal.list<any>('/shares/templates').catch(() => []),
    ]);
    cv.value = c;
    profile.value = p;
    templates.value = (t1 && t1.length ? t1 : t2) as any;
    if (c) {
        form.name = c.name || '';
        form.language = c.language || 'en';
        form.template_id = c.template_id ?? '';
        form.is_public = !!c.is_public;
    }
    loading.value = false;
});

async function onSave() {
    if (!cv.value) return;
    saving.value = true;
    const updated = await portal.update<CVSummary>(`/cvs/${id.value}`, {
        name: form.name,
        language: form.language,
        template_id: form.template_id || null,
        is_public: form.is_public,
    }, t('portal.cvs.saved'));
    if (updated) cv.value = { ...cv.value, ...updated };
    saving.value = false;
}

async function onDelete() {
    if (!cv.value) return;
    if (!confirm(t('portal.cvs.delete_confirm'))) return;
    const ok = await portal.destroy(`/cvs/${id.value}`, t('portal.cvs.deleted'));
    if (ok) router.push('/portal/cvs');
}

async function onDuplicate() {
    if (!cv.value) return;
    const r = await portal.duplicate(`/cvs/${id.value}/duplicate`);
    if (r?.id) router.push(`/portal/cvs/${r.id}/edit`);
}

const previewUrl = computed(() => {
    if (!cv.value) return '#';
    const config = useRuntimeConfig();
    return `${(config.public.laravelUrl as string).replace(/\/+$/, '')}/portal/cvs/${cv.value.id}/preview`;
});
</script>

<template>
    <header class="page-header">
        <div>
            <NuxtLink to="/portal/cvs" class="back-link">
                <Icon name="arrow-left" :size="13" /> {{ t('portal.cvs.back_to_cvs') }}
            </NuxtLink>
            <h1 class="page-header__title">{{ cv?.name || t('portal.cvs.not_found') }}</h1>
            <p class="page-header__subtitle" v-if="cv">
                {{ t('portal.cvs.edit_subtitle', { date: cv.updated_at ? new Date(cv.updated_at).toLocaleString() : '—' }) }}
            </p>
        </div>
        <div class="page-header__actions">
            <Button variant="secondary" @click="atsOpen = true">
                <Icon name="target" :size="14" /> {{ t('portal.cvs.check_ats') }}
            </Button>
            <a :href="previewUrl" target="_blank" rel="noopener" class="btn btn--secondary">
                <Icon name="eye" :size="14" /> {{ t('portal.cvs.preview') }}
            </a>
            <Button variant="secondary" @click="onDuplicate">
                <Icon name="copy" :size="14" /> {{ t('portal.cvs.duplicate') }}
            </Button>
            <Button variant="danger" @click="onDelete">
                <Icon name="trash" :size="14" />
            </Button>
        </div>
    </header>

    <div v-if="loading" class="empty">
        <span class="empty__icon"><Icon name="clock" :size="22" /></span>
        <p class="empty__title">{{ t('portal.common.loading') }}</p>
    </div>

    <form v-else class="surface form-card form-card--xl" @submit.prevent="onSave">
        <div class="field">
            <label class="field-label" for="name">{{ t('portal.cvs.field.name') }}</label>
            <input id="name" v-model="form.name" type="text" class="input" required maxlength="120" />
            <span class="field-hint">{{ t('portal.cvs.field.name_help') }}</span>
        </div>

        <div class="field-grid">
            <div class="field">
                <label class="field-label" for="language">{{ t('portal.cvs.field.language') }}</label>
                <select id="language" v-model="form.language" class="select">
                    <option v-for="l in langs" :key="l.code" :value="l.code">{{ l.name }}</option>
                </select>
            </div>
            <div class="field">
                <label class="field-label" for="template">{{ t('portal.cvs.field.template') }}</label>
                <select id="template" v-model="form.template_id" class="select">
                    <option value="">{{ t('portal.cvs.field.template_none') }}</option>
                    <option v-for="tpl in templates" :key="tpl.id" :value="tpl.id">{{ tpl.name }}</option>
                </select>
            </div>
        </div>

        <label class="checkbox" style="margin: 0.5rem 0 1.5rem;">
            <input v-model="form.is_public" type="checkbox" />
            <div>
                <span>{{ t('portal.cvs.field.public') }}</span>
                <span class="field-hint" style="margin-top: 0.15rem;">{{ t('portal.cvs.field.public_help') }}</span>
            </div>
        </label>

        <div class="form-actions">
            <Button type="submit" variant="primary" :loading="saving">
                {{ saving ? t('portal.cvs.saving') : t('portal.cvs.save') }}
            </Button>
        </div>
    </form>

    <AtsModal v-if="cv" v-model:open="atsOpen" :cv-id="cv.id" />
</template>

