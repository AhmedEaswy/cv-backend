<script setup lang="ts">
/**
 * /portal/settings — Profile and Password tabs.
 */
definePageMeta({ middleware: 'auth', layout: 'portal' });

const { t } = useI18n();
const route = useRoute();
const { user, refresh } = useAuthSession();
const api = useApi();
const toast = useToast();

const tab = computed(() => (route.query.tab === 'password' ? 'password' : 'profile'));

const profileForm = reactive({ name: '', first_name: '', last_name: '', email: '', phone: '' });
const pwdForm = reactive({ current_password: '', password: '', password_confirmation: '' });
const savingProfile = ref(false);
const savingPwd = ref(false);

onMounted(async () => {
    await refresh();
    if (user.value) {
        profileForm.name = user.value.name || '';
        profileForm.email = user.value.email || '';
        profileForm.first_name = user.value.first_name || '';
        profileForm.last_name = user.value.last_name || '';
        profileForm.phone = user.value.phone || '';
    }
});

async function saveProfile() {
    savingProfile.value = true;
    try {
        await api('/auth/me', { method: 'PUT', body: { ...profileForm } }).catch(() => null);
        await api('/profile', { method: 'POST', body: { ...profileForm } });
        await refresh();
        toast.success(t('portal.settings.profile.saved'));
    } catch (e: any) {
        toast.error(e?.data?.message || 'Could not save');
    } finally {
        savingProfile.value = false;
    }
}

async function savePassword() {
    savingPwd.value = true;
    try {
        await api('/auth/password', { method: 'PUT', body: { ...pwdForm } }).catch(async () => {
            await api('/settings/password', { method: 'PUT', body: { ...pwdForm } });
        });
        toast.success(t('portal.settings.password.saved'));
        pwdForm.current_password = '';
        pwdForm.password = '';
        pwdForm.password_confirmation = '';
    } catch (e: any) {
        toast.error(e?.data?.message || t('portal.settings.password.wrong_current'));
    } finally {
        savingPwd.value = false;
    }
}
</script>

<template>
    <header class="page-header">
        <div>
            <div class="page-header__eyebrow">{{ t('portal.nav.settings') }}</div>
            <h1 class="page-header__title">{{ t('portal.nav.settings') }}</h1>
        </div>
    </header>

    <SettingsTabs />

    <form v-if="tab === 'profile'" class="surface form-card form-card--xl" @submit.prevent="saveProfile">
        <h2 class="form-card__title">{{ t('portal.settings.profile.title') }}</h2>
        <p class="form-card__sub">{{ t('portal.settings.profile.subtitle') }}</p>

        <div class="field-grid">
            <div class="field">
                <label class="field-label" for="first_name">{{ t('portal.settings.profile.field.first_name') }}</label>
                <input id="first_name" v-model="profileForm.first_name" class="input" :placeholder="t('portal.settings.profile.field.first_name_placeholder')" />
            </div>
            <div class="field">
                <label class="field-label" for="last_name">{{ t('portal.settings.profile.field.last_name') }}</label>
                <input id="last_name" v-model="profileForm.last_name" class="input" :placeholder="t('portal.settings.profile.field.last_name_placeholder')" />
            </div>
        </div>
        <div class="field">
            <label class="field-label" for="name">{{ t('portal.settings.profile.field.name') }}</label>
            <input id="name" v-model="profileForm.name" class="input" required :placeholder="t('portal.settings.profile.field.name_placeholder')" />
        </div>
        <div class="field-grid">
            <div class="field">
                <label class="field-label" for="email">{{ t('portal.settings.profile.field.email') }}</label>
                <input id="email" v-model="profileForm.email" type="email" class="input" required :placeholder="t('portal.settings.profile.field.email_placeholder')" />
            </div>
            <div class="field">
                <label class="field-label" for="phone">{{ t('portal.settings.profile.field.phone') }}</label>
                <input id="phone" v-model="profileForm.phone" type="tel" class="input" :placeholder="t('portal.settings.profile.field.phone_placeholder')" />
            </div>
        </div>
        <div class="form-actions">
            <Button type="submit" variant="primary" :loading="savingProfile">
                {{ savingProfile ? t('portal.settings.profile.saving') : t('portal.settings.profile.save') }}
            </Button>
        </div>
    </form>

    <form v-else class="surface form-card form-card--xl" @submit.prevent="savePassword">
        <h2 class="form-card__title">{{ t('portal.settings.password.title') }}</h2>
        <p class="form-card__sub">{{ t('portal.settings.password.subtitle') }}</p>

        <div class="field">
            <label class="field-label" for="current">{{ t('portal.settings.password.current') }}</label>
            <input id="current" v-model="pwdForm.current_password" type="password" class="input" autocomplete="current-password" required :placeholder="t('portal.settings.password.current_placeholder')" />
        </div>
        <div class="field-grid">
            <div class="field">
                <label class="field-label" for="new">{{ t('portal.settings.password.new') }}</label>
                <input id="new" v-model="pwdForm.password" type="password" class="input" autocomplete="new-password" required :placeholder="t('portal.settings.password.new_placeholder')" />
            </div>
            <div class="field">
                <label class="field-label" for="confirm">{{ t('portal.settings.password.confirm') }}</label>
                <input id="confirm" v-model="pwdForm.password_confirmation" type="password" class="input" autocomplete="new-password" required :placeholder="t('portal.settings.password.confirm_placeholder')" />
            </div>
        </div>
        <div class="form-actions">
            <Button type="submit" variant="primary" :loading="savingPwd">
                {{ savingPwd ? t('portal.settings.password.saving') : t('portal.settings.password.save') }}
            </Button>
        </div>
    </form>
</template>
