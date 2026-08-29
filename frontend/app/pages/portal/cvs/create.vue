<script setup lang="ts">
/**
 * /portal/cvs/create — minimal create form. After create we redirect
 * straight to the edit page so the user can fill the rest.
 */
definePageMeta({ middleware: 'auth', layout: 'portal' });

const { t } = useI18n();
const router = useRouter();
const portal = usePortalApi();
const loading = ref(false);

const form = reactive({ name: '', language: 'en' });
const langs = [
    { code: 'en', name: 'English' },
    { code: 'ar', name: 'العربية' },
    { code: 'tr', name: 'Türkçe' },
    { code: 'es', name: 'Español' },
    { code: 'fr', name: 'Français' },
    { code: 'de', name: 'Deutsch' },
    { code: 'ur', name: 'اردو' },
];

async function onSubmit() {
    if (!form.name.trim()) return;
    loading.value = true;
    const created = await portal.create<any>('/cvs', { name: form.name, language: form.language });
    loading.value = false;
    if (created?.id) router.push(`/portal/cvs/${created.id}/edit`);
}
</script>

<template>
    <header class="page-header">
        <div>
            <div class="page-header__eyebrow">{{ t('portal.cvs.eyebrow') }}</div>
            <h1 class="page-header__title">{{ t('portal.cvs.create_title') }}</h1>
            <p class="page-header__subtitle">{{ t('portal.cvs.create_subtitle') }}</p>
        </div>
        <div class="page-header__actions">
            <NuxtLink to="/portal/cvs" class="btn btn--ghost">
                <Icon name="arrow-left" :size="14" /> {{ t('portal.common.back') }}
            </NuxtLink>
        </div>
    </header>

    <form class="surface form-card" @submit.prevent="onSubmit">
        <div class="field">
            <label class="field-label" for="name">{{ t('portal.cvs.field.name') }}</label>
            <input id="name" v-model="form.name" type="text" class="input" required maxlength="120" />
            <span class="field-hint">{{ t('portal.cvs.field.name_help') }}</span>
        </div>
        <div class="field">
            <label class="field-label" for="language">{{ t('portal.cvs.field.language') }}</label>
            <select id="language" v-model="form.language" class="select">
                <option v-for="l in langs" :key="l.code" :value="l.code">{{ l.name }}</option>
            </select>
        </div>
        <div class="form-actions">
            <Button type="submit" variant="primary" :loading="loading">
                {{ loading ? t('portal.cvs.creating') : t('portal.cvs.create_action') }}
            </Button>
        </div>
    </form>
</template>

<style scoped>
.form-card { padding: 1.75rem; max-width: 32rem; }
.field { margin-bottom: 1.25rem; }
.form-actions { display: flex; justify-content: flex-end; gap: 0.5rem; }
</style>
