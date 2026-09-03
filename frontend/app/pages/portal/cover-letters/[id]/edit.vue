<script setup lang="ts">
definePageMeta({ middleware: 'auth', layout: 'portal' });
import type { CoverLetterSummary } from '~/composables/usePortalApi';

const { t } = useI18n();
const route = useRoute();
const router = useRouter();
const portal = usePortalApi();

const id = computed(() => Number(route.params.id));
const letter = ref<CoverLetterSummary | null>(null);
const templates = ref<Array<{ id: number; name: string }>>([]);
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
        portal.list<any>('/cover-letters/templates').catch(() => []),
        portal.list<any>('/shares/templates').catch(() => []),
    ]);
    letter.value = l;
    templates.value = (t1 && t1.length ? t1 : t2) as any;
    if (l) {
        form.name = l.name || '';
        form.company = l.company || '';
        form.role = l.role || '';
        form.template_id = l.template_id ?? '';
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
    <header class="page-header">
        <div>
            <NuxtLink to="/portal/cover-letters" class="back-link">
                <Icon name="arrow-left" :size="13" /> {{ t('portal.cover_letters.back') }}
            </NuxtLink>
            <h1 class="page-header__title">{{ letter?.name || t('portal.cvs.not_found') }}</h1>
            <p class="page-header__subtitle" v-if="letter">
                {{ t('portal.cover_letters.edit_subtitle', { date: letter.updated_at ? new Date(letter.updated_at).toLocaleString() : '—' }) }}
            </p>
        </div>
        <div class="page-header__actions">
            <Button variant="danger" @click="onDelete">
                <Icon name="trash" :size="14" />
            </Button>
        </div>
    </header>

    <div v-if="loading" class="empty">
        <span class="empty__icon"><Icon name="clock" :size="22" /></span>
        <p class="empty__title">{{ t('portal.common.loading') }}</p>
    </div>

    <form v-else class="surface form-card form-card--wide" @submit.prevent="onSave">
        <div class="field">
            <label class="field-label" for="name">{{ t('portal.cover_letters.field.name') }}</label>
            <input id="name" v-model="form.name" type="text" class="input" required maxlength="120" />
        </div>
        <div class="field-grid">
            <div class="field">
                <label class="field-label" for="company">{{ t('portal.cover_letters.field.company') }}</label>
                <input id="company" v-model="form.company" type="text" class="input" />
            </div>
            <div class="field">
                <label class="field-label" for="role">{{ t('portal.cover_letters.field.role') }}</label>
                <input id="role" v-model="form.role" type="text" class="input" />
            </div>
        </div>
        <div class="field">
            <label class="field-label" for="template">{{ t('portal.cover_letters.field.template') }}</label>
            <select id="template" v-model="form.template_id" class="select">
                <option value="">{{ t('portal.cvs.field.template_none') }}</option>
                <option v-for="tpl in templates" :key="tpl.id" :value="tpl.id">{{ tpl.name }}</option>
            </select>
        </div>
        <div class="field">
            <label class="field-label" for="body">{{ t('portal.cover_letters.field.body') }}</label>
            <textarea id="body" v-model="form.body" class="textarea" rows="14" />
        </div>
        <div class="form-actions">
            <Button type="submit" variant="primary" :loading="saving">
                {{ saving ? t('portal.cover_letters.saving') : t('portal.cover_letters.save') }}
            </Button>
        </div>
    </form>
</template>

