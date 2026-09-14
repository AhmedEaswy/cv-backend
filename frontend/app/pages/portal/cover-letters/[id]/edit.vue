<script setup lang="ts">
/**
 * /portal/cover-letters/[id]/edit — cover letter editor with live template preview.
 */
import { watchDebounced } from '@vueuse/core';
import type { CoverLetterSummary } from '~/composables/usePortalApi';
import type { TemplateOption } from '~/components/portal/cv/CvTemplateSlider.vue';

definePageMeta({ middleware: 'auth', layout: 'portal' });

const { t } = useI18n();
const route = useRoute();
const router = useRouter();
const portal = usePortalApi();

const id = computed(() => Number(route.params.id));
const letter = ref<CoverLetterSummary | null>(null);
const templates = ref<TemplateOption[]>([]);
const loading = ref(true);
const saving = ref(false);
const autosaving = ref(false);
const previewRevision = ref(0);
const previewReady = ref(false);
/** Suppress autosave while hydrating from the server. */
const syncingForm = ref(false);

const form = reactive({
    name: '',
    company: '',
    role: '',
    template_id: '' as string | number,
    body: '',
});

function applyLetter(l: CoverLetterSummary) {
    syncingForm.value = true;
    const userData = (l.user_data || {}) as Record<string, unknown>;
    form.name = l.name || '';
    form.company = String(
        userData.companyName
        || userData.recipientCompany
        || l.company
        || '',
    );
    form.role = String(userData.jobTitle || l.role || '');
    form.body = String(userData.body || l.body || '');
    const defaultTpl = templates.value.find((tpl) => tpl.is_default);
    form.template_id = l.cover_letter_template_id
        ?? l.template_id
        ?? defaultTpl?.id
        ?? '';
    nextTick(() => {
        syncingForm.value = false;
    });
}

function payload() {
    return {
        name: form.name,
        cover_letter_template_id: form.template_id || null,
        user_data: {
            companyName: form.company || null,
            recipientCompany: form.company || null,
            jobTitle: form.role || null,
            body: form.body || null,
        },
    };
}

onMounted(async () => {
    const [l, t1, t2] = await Promise.all([
        portal.show<CoverLetterSummary>(`/cover-letters/${id.value}`),
        portal.list<TemplateOption>('/cover-letters/templates').catch(() => []),
        portal.list<TemplateOption>('/shares/templates').catch(() => []),
    ]);
    letter.value = l;
    templates.value = (t1 && t1.length ? t1 : t2);
    if (l) {
        applyLetter(l);
        previewRevision.value = Date.now();
        previewReady.value = true;
    }
    loading.value = false;
});

async function persist(opts?: { silent?: boolean }) {
    if (!letter.value) return null;
    const updated = await portal.update<CoverLetterSummary>(
        `/cover-letters/${id.value}`,
        payload(),
        t('portal.cover_letters.saved'),
        { silent: opts?.silent },
    );
    if (updated) {
        letter.value = { ...letter.value, ...updated };
        if (!opts?.silent) {
            applyLetter(letter.value);
        }
        previewRevision.value = Date.now();
    }
    return updated;
}

async function onSave() {
    if (!letter.value) return;
    saving.value = true;
    await persist();
    saving.value = false;
}

watchDebounced(
    [() => form.name, () => form.company, () => form.role, () => form.body, () => form.template_id],
    async () => {
        if (!previewReady.value || !letter.value || saving.value || autosaving.value || syncingForm.value) return;
        autosaving.value = true;
        await persist({ silent: true });
        autosaving.value = false;
    },
    { debounce: 900 },
);

async function onDelete() {
    if (!letter.value) return;
    if (!confirm(t('portal.cover_letters.delete_confirm'))) return;
    const ok = await portal.destroy(`/cover-letters/${id.value}`, t('portal.cover_letters.deleted'));
    if (ok) router.push('/portal/cover-letters');
}
</script>

<template>
    <FormSkeleton v-if="loading" :fields="5" wide with-header />

    <div v-else-if="!letter" class="empty">
        <span class="empty__icon"><Icon name="mail" :size="22" /></span>
        <p class="empty__title">{{ t('portal.cvs.not_found') }}</p>
        <NuxtLink to="/portal/cover-letters" class="btn btn--secondary">
            <Icon name="arrow-left" :size="13" /> {{ t('portal.cover_letters.back') }}
        </NuxtLink>
    </div>

    <template v-else>
    <header class="page-header">
        <div>
            <NuxtLink to="/portal/cover-letters" class="back-link">
                <Icon name="arrow-left" :size="13" /> {{ t('portal.cover_letters.back') }}
            </NuxtLink>
            <h1 class="page-header__title">{{ letter.name }}</h1>
            <p class="page-header__subtitle">
                {{ t('portal.cover_letters.edit_subtitle', { date: letter.updated_at ? new Date(letter.updated_at).toLocaleString() : '—' }) }}
            </p>
        </div>
        <div class="page-header__actions">
            <Button variant="danger" icon @click="onDelete" :aria-label="t('portal.cover_letters.delete')">
                <Icon name="trash" :size="14" />
            </Button>
        </div>
    </header>

    <form class="cv-builder" @submit.prevent="onSave">
        <div class="cv-builder__main">
            <div class="surface form-card">
                <div class="field">
                    <label class="field-label" for="name">{{ t('portal.cover_letters.field.name') }}</label>
                    <input id="name" v-model="form.name" type="text" class="input" required maxlength="120" :placeholder="t('portal.cover_letters.field.name_placeholder')" />
                </div>
                <div class="field-grid">
                    <div class="field">
                        <label class="field-label" for="company">{{ t('portal.cover_letters.field.company') }}</label>
                        <input id="company" v-model="form.company" type="text" class="input" :placeholder="t('portal.cover_letters.field.company_placeholder')" />
                    </div>
                    <div class="field">
                        <label class="field-label" for="role">{{ t('portal.cover_letters.field.role') }}</label>
                        <input id="role" v-model="form.role" type="text" class="input" :placeholder="t('portal.cover_letters.field.role_placeholder')" />
                    </div>
                </div>
                <div class="field">
                    <label class="field-label" for="body">{{ t('portal.cover_letters.field.body') }}</label>
                    <textarea id="body" v-model="form.body" class="textarea" rows="14" :placeholder="t('portal.cover_letters.field.body_placeholder')" />
                </div>
            </div>
        </div>

        <aside class="cv-builder__side">
            <div class="surface form-card cv-builder__meta">
                <div class="field">
                    <span class="field-label">{{ t('portal.cover_letters.field.template') }}</span>
                    <CvTemplateSlider v-model="form.template_id" :templates="templates" kind="cover-letter" />
                </div>
            </div>

            <TemplateLivePreview
                kind="cover-letter"
                :document-id="letter.id"
                :template-id="form.template_id"
                :revision="previewRevision"
                :loading="autosaving"
            />
        </aside>

        <div class="cv-builder__save">
            <Button type="submit" variant="primary" :loading="saving">
                {{ saving ? t('portal.cover_letters.saving') : t('portal.cover_letters.save') }}
            </Button>
        </div>
    </form>
    </template>
</template>
