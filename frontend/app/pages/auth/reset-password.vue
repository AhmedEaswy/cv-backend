<script setup lang="ts">
/**
 * /auth/reset-password?token=…&email=…
 */
const { t } = useI18n();
const route = useRoute();
const { reset, loading, fieldError, generalError } = useAuthSession();

const form = reactive({
    token: (route.query.token as string) || '',
    email: (route.query.email as string) || '',
    password: '',
    password_confirmation: '',
});

const success = ref(false);
async function onSubmit() {
    const r = await reset({ ...form });
    if (r.ok) success.value = true;
}
</script>

<template>
    <NuxtLayout name="auth">
        <template #aside>
            <h2>{{ t('auth.reset.aside.title') }}</h2>
            <p>{{ t('auth.reset.aside.text') }}</p>
        </template>

        <h1 class="auth-form__title">{{ t('auth.reset.title') }}</h1>
        <p class="auth-form__subtitle">{{ t('auth.reset.subtitle') }}</p>

        <Alert v-if="success" variant="success">{{ t('auth.reset.success') }}</Alert>
        <Alert v-else-if="!form.token || !form.email" variant="error">{{ t('auth.reset.invalid_token') }}</Alert>
        <Alert v-else-if="generalError" variant="error">{{ generalError }}</Alert>

        <form v-if="!success && form.token && form.email" @submit.prevent="onSubmit" novalidate>
            <div class="field">
                <label class="field-label" for="email">{{ t('auth.email') }}</label>
                <input id="email" v-model="form.email" type="email" class="input" required />
            </div>
            <div class="field">
                <label class="field-label" for="password">{{ t('auth.reset.password') }}</label>
                <input id="password" v-model="form.password" type="password" class="input" autocomplete="new-password" required :aria-invalid="!!fieldError('password')" />
                <span v-if="fieldError('password')" class="field-error">{{ fieldError('password') }}</span>
            </div>
            <div class="field">
                <label class="field-label" for="password_confirmation">{{ t('auth.reset.password_confirm') }}</label>
                <input id="password_confirmation" v-model="form.password_confirmation" type="password" class="input" autocomplete="new-password" required />
            </div>
            <Button type="submit" variant="primary" :loading="loading" block>
                {{ loading ? t('auth.reset.submitting') : t('auth.reset.action') }}
            </Button>
        </form>

        <div class="auth-foot">
            <NuxtLink to="/auth/login" class="auth-link">
                <Icon name="arrow-left" :size="12" /> {{ t('auth.signin') }}
            </NuxtLink>
        </div>
    </NuxtLayout>
</template>

<style scoped>
.field { margin-bottom: 1rem; }
.auth-foot { margin-top: 1.5rem; font-size: 0.85rem; display: flex; gap: 0.5rem; align-items: center; }
.auth-foot :deep(a) { display: inline-flex; align-items: center; gap: 0.3rem; }
</style>
