<script setup lang="ts">
/**
 * /auth/forgot-password — request a password-reset OTP by email.
 */
const { t } = useI18n();
const { forgot, loading, fieldError, generalError } = useAuthSession();

const form = reactive({ email: '' });

async function onSubmit() {
    await forgot({ email: form.email });
}
</script>

<template>
    <NuxtLayout name="auth">
        <template #aside>
            <h2>{{ t('auth.forgot.aside.title') }}</h2>
            <p>{{ t('auth.forgot.aside.text') }}</p>
        </template>

        <h1 class="auth-form__title">{{ t('auth.forgot.title') }}</h1>
        <p class="auth-form__subtitle">{{ t('auth.forgot.subtitle') }}</p>

        <Alert v-if="generalError" variant="error">{{ generalError }}</Alert>

        <form @submit.prevent="onSubmit" novalidate>
            <div class="field">
                <label class="field-label" for="email">{{ t('auth.email') }}</label>
                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="input"
                    autocomplete="email"
                    required
                    :placeholder="t('auth.email_placeholder')"
                    :aria-invalid="!!fieldError('email')"
                />
                <span v-if="fieldError('email')" class="field-error">{{ fieldError('email') }}</span>
            </div>
            <Button type="submit" variant="primary" :loading="loading" block>
                {{ loading ? t('auth.forgot.submitting') : t('auth.forgot.action') }}
            </Button>
        </form>

        <div class="auth-foot">
            <NuxtLink to="/auth/login" class="auth-link">
                <Icon name="arrow-left" :size="12" /> {{ t('auth.forgot.back_to_login') }}
            </NuxtLink>
        </div>
    </NuxtLayout>
</template>
