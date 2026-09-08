<script setup lang="ts">
/**
 * /portal/cvs/[id]/edit — section-based CV builder (mobile parity).
 */
import type { CVSummary } from '~/composables/usePortalApi';
import {
    compactCvUserData,
    DEFAULT_CV_SECTIONS,
    normalizeCvUserData,
    resolvePublicFileUrl,
    type CvUserData,
} from '~/composables/usePortalApi';
import type { CvTemplateOption } from '~/components/portal/cv/CvTemplateSlider.vue';
import type { DropdownMenuItem } from '~/components/ui/DropdownMenu.vue';

definePageMeta({ middleware: 'auth', layout: 'portal' });

const { t } = useI18n();
const route = useRoute();
const router = useRouter();
const portal = usePortalApi();
const toast = useToast();

const id = computed(() => Number(route.params.id));
const cv = ref<CVSummary | null>(null);
const templates = ref<CvTemplateOption[]>([]);
const loading = ref(true);
const saving = ref(false);
const printing = ref(false);
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

const userData = ref<CvUserData>(normalizeCvUserData());
const sectionsOrder = ref<string[]>([...DEFAULT_CV_SECTIONS]);

const actionItems = computed<DropdownMenuItem[]>(() => [
    { key: 'duplicate', label: t('portal.cvs.duplicate'), icon: 'copy' },
    { key: 'preview', label: t('portal.cvs.preview'), icon: 'eye', disabled: printing.value },
    { key: 'download', label: t('portal.cvs.download'), icon: 'download', disabled: printing.value },
    { key: 'delete', label: t('portal.cvs.delete'), icon: 'trash', danger: true },
]);

onMounted(async () => {
    const [c, tpls] = await Promise.all([
        portal.show<CVSummary>(`/cvs/${id.value}`),
        portal.list<CvTemplateOption>('/shares/templates'),
    ]);
    cv.value = c;
    templates.value = tpls;
    if (c) {
        form.name = c.name || '';
        form.language = c.language || 'en';
        const defaultTpl = tpls.find((tpl) => tpl.is_default);
        form.template_id = c.template_id ?? defaultTpl?.id ?? '';
        form.is_public = !!c.is_public;
        userData.value = normalizeCvUserData(c.user_data);
        sectionsOrder.value = (c.sections_order && c.sections_order.length)
            ? [...c.sections_order]
            : [...DEFAULT_CV_SECTIONS];
    }
    loading.value = false;
});

function payload() {
    return {
        name: form.name,
        language: form.language,
        template_id: form.template_id || null,
        is_public: form.is_public,
        sections_order: sectionsOrder.value,
        user_data: compactCvUserData(userData.value),
    };
}

async function onSave() {
    if (!cv.value) return;
    saving.value = true;
    const updated = await portal.update<CVSummary>(`/cvs/${id.value}`, payload(), t('portal.cvs.saved'));
    if (updated) {
        cv.value = { ...cv.value, ...updated };
        if (updated.user_data) userData.value = normalizeCvUserData(updated.user_data);
        if (updated.sections_order?.length) sectionsOrder.value = [...updated.sections_order];
    }
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

async function generatePdf(mode: 'preview' | 'download') {
    if (!cv.value) return;
    if (!form.template_id) {
        toast.error(t('portal.cvs.pdf_need_template'));
        return;
    }
    printing.value = true;
    const saved = await portal.update<CVSummary>(`/cvs/${id.value}`, payload(), t('portal.cvs.saved'));
    if (saved) {
        cv.value = { ...cv.value, ...saved };
        if (saved.user_data) userData.value = normalizeCvUserData(saved.user_data);
    }
    const printed = await portal.printCv({
        profile_id: cv.value.id,
        template_id: Number(form.template_id),
    });
    if (printed?.url) {
        const url = resolvePublicFileUrl(printed.url);
        if (mode === 'download') {
            await downloadFile(url, `${form.name || 'cv'}.pdf`);
        } else {
            window.open(url, '_blank', 'noopener');
        }
    }
    printing.value = false;
}

async function downloadFile(url: string, filename: string) {
    try {
        const res = await fetch(url);
        if (!res.ok) throw new Error('fetch failed');
        const blob = await res.blob();
        const objectUrl = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = objectUrl;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        a.remove();
        URL.revokeObjectURL(objectUrl);
    } catch {
        window.open(url, '_blank', 'noopener');
    }
}

function onAction(key: string) {
    if (key === 'duplicate') return onDuplicate();
    if (key === 'preview') return generatePdf('preview');
    if (key === 'download') return generatePdf('download');
    if (key === 'delete') return onDelete();
}
</script>

<template>
    <CvFormSkeleton v-if="loading" />

    <div v-else-if="!cv" class="empty">
        <span class="empty__icon"><Icon name="file" :size="22" /></span>
        <p class="empty__title">{{ t('portal.cvs.not_found') }}</p>
        <NuxtLink to="/portal/cvs" class="btn btn--secondary">
            <Icon name="arrow-left" :size="13" /> {{ t('portal.cvs.back_to_cvs') }}
        </NuxtLink>
    </div>

    <template v-else>
    <header class="page-header">
        <div>
            <NuxtLink to="/portal/cvs" class="back-link">
                <Icon name="arrow-left" :size="13" /> {{ t('portal.cvs.back_to_cvs') }}
            </NuxtLink>
            <h1 class="page-header__title">{{ cv.name }}</h1>
            <p class="page-header__subtitle">
                {{ t('portal.cvs.edit_subtitle', { date: cv.updated_at ? new Date(cv.updated_at).toLocaleString() : '—' }) }}
            </p>
        </div>
        <div class="page-header__actions">
            <Button variant="secondary" @click="atsOpen = true">
                <Icon name="target" :size="14" /> {{ t('portal.cvs.check_ats') }}
            </Button>
            <DropdownMenu
                variant="secondary"
                :items="actionItems"
                :label="t('portal.cvs.actions')"
                @select="onAction"
            />
        </div>
    </header>

    <form class="cv-builder" @submit.prevent="onSave">
        <div class="cv-builder__main">
            <CvSectionHub v-model:user-data="userData" v-model:sections-order="sectionsOrder" />
        </div>

        <aside class="cv-builder__side">
            <div class="surface form-card cv-builder__meta">
                <div class="field">
                    <label class="field-label" for="name">{{ t('portal.cvs.field.name') }}</label>
                    <input id="name" v-model="form.name" type="text" class="input" required maxlength="120" :placeholder="t('portal.cvs.field.name_placeholder')" />
                    <span class="field-hint">{{ t('portal.cvs.field.name_help') }}</span>
                </div>

                <div class="field">
                    <label class="field-label" for="language">{{ t('portal.cvs.field.language') }}</label>
                    <SelectInput
                        id="language"
                        v-model="form.language"
                        :options="langs.map((l) => ({ value: l.code, label: l.name }))"
                    />
                </div>

                <div class="field">
                    <span class="field-label">{{ t('portal.cvs.field.template') }}</span>
                    <CvTemplateSlider v-model="form.template_id" :templates="templates" kind="cv" />
                </div>

                <Switch v-model="form.is_public">
                    <span>{{ t('portal.cvs.field.public') }}</span>
                    <span class="field-hint">{{ t('portal.cvs.field.public_help') }}</span>
                </Switch>
            </div>
        </aside>

        <div class="cv-builder__save">
            <Button type="submit" variant="primary" :loading="saving">
                {{ saving ? t('portal.cvs.saving') : t('portal.cvs.save') }}
            </Button>
        </div>
    </form>

    <AtsModal v-model:open="atsOpen" :cv-id="cv.id" />
    </template>
</template>
