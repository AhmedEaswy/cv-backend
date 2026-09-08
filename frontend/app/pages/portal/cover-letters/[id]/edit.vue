<script setup lang="ts">
definePageMeta({ middleware: 'auth', layout: 'portal' });
import type { CoverLetterSummary } from '~/composables/usePortalApi';
import type { TemplateOption } from '~/components/portal/cv/CvTemplateSlider.vue';

const { t } = useI18n();
const route = useRoute();
const router = useRouter();
const portal = usePortalApi();

const id = computed(() => Number(route.params.id));
const letter = ref<CoverLetterSummary | null>(null);
const templates = ref<TemplateOption[]>([]);
const loading = ref(true);
const saving = ref(false);

const form = reactive({
    name: '',
    company: '',
    role: '',
    template_id: '' as string | number,
    body: '',
});

onMounted(async () => {
    const [l, t1, t2] = await Promise.all([
        portal.show<CoverLetterSummary>(`/cover-letters/${id.value}`),
        portal.list<TemplateOption>('/cover-letters/templates').catch(() => []),
        portal.list<TemplateOption>('/shares/templates').catch(() => []),
    ]);
    letter.value = l;
    templates.value = (t1 && t1.length ? t1 : t2);
    if (l) {
        form.name = l.name || '';
        form.company = l.company || '';
        form.role = l.role || '';
        const defaultTpl = templates.value.find((tpl) => tpl.is_default);
        form.template_id = l.template_id ?? defaultTpl?.id ?? '';
        form.body = l.body || '';
    }
    loading.value = false;
});

async function onSave() {
    if (!letter.value) return;
    saving.value = true;
    const updated = await portal.update(`/cover-letters/${id.value}`, {
        name: form.name,
        company: form.company,
        role: form.role,
        template_id: form.template_id || null,
        body: form.body,
    }, t('portal.cover_letters.saved'));
    if (updated) letter.value = { ...letter.value, ...(updated as any) };
    saving.value = false;
}

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
        </aside>

        <div class="cv-builder__save">
            <Button type="submit" variant="primary" :loading="saving">
                {{ saving ? t('portal.cover_letters.saving') : t('portal.cover_letters.save') }}
            </Button>
        </div>
    </form>
    </template>
</template>

