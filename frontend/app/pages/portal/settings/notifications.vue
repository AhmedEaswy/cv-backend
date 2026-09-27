<script setup lang="ts">
definePageMeta({ middleware: 'auth', layout: 'portal' });

const { t } = useI18n();
const api = useApi();
const toast = useToast();

const loading = ref(true);
const saving = ref(false);
const form = reactive({
    notify_contact_email: true,
    notify_contact_push: true,
});

async function load() {
    loading.value = true;
    try {
        const res = await api<{
            result?: { notify_contact_email?: boolean; notify_contact_push?: boolean };
        }>('/settings/notifications');
        const data = res?.result;
        if (data) {
            form.notify_contact_email = !!data.notify_contact_email;
            form.notify_contact_push = !!data.notify_contact_push;
        }
    } finally {
        loading.value = false;
    }
}

onMounted(load);

async function save() {
    saving.value = true;
    try {
        await api('/settings/notifications', {
            method: 'PUT',
            body: { ...form },
        });
        toast.success(t('portal.settings.notifications.saved'));
    } catch (e: any) {
        toast.error(e?.data?.message || 'Could not save');
    } finally {
        saving.value = false;
    }
}
</script>

<template>
    <header class="page-header">
        <div>
            <div class="page-header__eyebrow">{{ t('portal.nav.settings') }}</div>
            <h1 class="page-header__title">{{ t('portal.settings.notifications.title') }}</h1>
            <p class="page-header__subtitle">{{ t('portal.settings.notifications.subtitle') }}</p>
        </div>
    </header>

    <SettingsTabs />

    <FormSkeleton v-if="loading" :fields="2" />

    <section v-else class="surface form-card form-card--xl">
        <form @submit.prevent="save">
            <div class="field">
                <Switch v-model="form.notify_contact_email" size="sm">
                    <span>{{ t('portal.settings.notifications.email') }}</span>
                </Switch>
                <span class="field-hint">{{ t('portal.settings.notifications.email_help') }}</span>
            </div>
            <div class="field">
                <Switch v-model="form.notify_contact_push" size="sm">
                    <span>{{ t('portal.settings.notifications.push') }}</span>
                </Switch>
                <span class="field-hint">{{ t('portal.settings.notifications.push_help') }}</span>
            </div>
            <Button type="submit" variant="primary" :loading="saving">
                {{ t('portal.settings.notifications.save') }}
            </Button>
        </form>
    </section>
</template>
