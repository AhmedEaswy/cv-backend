<script setup lang="ts">
definePageMeta({ middleware: 'auth', layout: 'portal' });

const { t } = useI18n();
const router = useRouter();
const portal = usePortalApi();
const loading = ref(false);

const form = reactive({ name: '' });
async function onSubmit() {
    if (!form.name.trim()) return;
    loading.value = true;
    const created = await portal.create<any>('/cover-letters', { name: form.name });
    loading.value = false;
    if (created?.id) router.push(`/portal/cover-letters/${created.id}/edit`);
}
</script>

<template>
    <header class="page-header">
        <div>
            <div class="page-header__eyebrow">{{ t('portal.cover_letters.eyebrow') }}</div>
            <h1 class="page-header__title">{{ t('portal.cover_letters.create_title') }}</h1>
            <p class="page-header__subtitle">{{ t('portal.cover_letters.create_subtitle') }}</p>
        </div>
        <div class="page-header__actions">
            <NuxtLink to="/portal/cover-letters" class="btn btn--ghost">
                <Icon name="arrow-left" :size="14" /> {{ t('portal.common.back') }}
            </NuxtLink>
        </div>
    </header>

    <form class="surface form-card" @submit.prevent="onSubmit">
        <div class="field">
            <label class="field-label" for="name">{{ t('portal.cover_letters.field.name') }}</label>
            <input id="name" v-model="form.name" type="text" class="input" required maxlength="120" />
        </div>
        <div class="form-actions">
            <Button type="submit" variant="primary" :loading="loading">
                {{ loading ? t('portal.cover_letters.creating') : t('portal.cover_letters.create_action') }}
            </Button>
        </div>
    </form>
</template>

