<script setup lang="ts">
/**
 * /auth/register
 */
const { t } = useI18n();
const config = useRuntimeConfig();
const laravel = (config.public.laravelUrl as string).replace(/\/+$/, '');
const { register, loading, fieldError, generalError } = useAuthSession();

const form = reactive({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});
const onSubmit = () => register({ ...form });
</script>

<template>
    <NuxtLayout name="auth">
        <template #aside>
            <h2>{{ t('auth.register.aside.title') }}</h2>
            <p>{{ t('auth.register.aside.text') }}</p>
            <ul>
                <li>
                    <Icon name="check" :size="16" />
                    <span>{{ t('auth.register.aside.bullet_1') }}</span>
                </li>
                <li>
                    <Icon name="check" :size="16" />
                    <span>{{ t('auth.register.aside.bullet_2') }}</span>
                </li>
                <li>
                    <Icon name="check" :size="16" />
                    <span>{{ t('auth.register.aside.bullet_3') }}</span>
                </li>
            </ul>
        </template>

        <h1 class="auth-form__title">{{ t('auth.register.title') }}</h1>
        <p class="auth-form__subtitle">{{ t('auth.register.subtitle') }}</p>

        <Alert v-if="generalError" variant="error">{{ generalError }}</Alert>

        <a :href="laravel + '/auth/google/redirect'" class="btn btn--secondary btn--block">
            <Icon name="google" :size="18" />
            {{ t('auth.register.continue_google') }}
        </a>

        <div class="auth-divider">{{ t('auth.or') }}</div>

        <form @submit.prevent="onSubmit" novalidate>
            <div class="field">
                <label class="field-label" for="name">{{ t('auth.register.name') }}</label>
                <input
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="input"
                    autocomplete="name"
                    required
                    :placeholder="t('auth.name_placeholder')"
                    :aria-invalid="!!fieldError('name')"
                />
                <span v-if="fieldError('name')" class="field-error">{{ fieldError('name') }}</span>
            </div>

            <div class="field">
                <label class="field-label" for="email">{{ t('auth.register.email') }}</label>
                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="input"
                    autocomplete="email"
                    inputmode="email"
                    required
                    :placeholder="t('auth.email_placeholder')"
                    :aria-invalid="!!fieldError('email')"
                />
                <span v-if="fieldError('email')" class="field-error">{{ fieldError('email') }}</span>
            </div>

            <div class="field">
                <label class="field-label" for="password">{{ t('auth.register.password') }}</label>
                <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="input"
                    autocomplete="new-password"
                    required
                    :placeholder="t('auth.password_new_placeholder')"
                    :aria-invalid="!!fieldError('password')"
                />
                <span class="field-hint">
                    {{ t('auth.register.terms_prefix') }}
                    <NuxtLink to="/terms">{{ t('landing.footer_terms') }}</NuxtLink>
                    {{ t('auth.register.terms_and') }}
                    <NuxtLink to="/privacy">{{ t('landing.footer_privacy') }}</NuxtLink>.
                </span>
                <span v-if="fieldError('password')" class="field-error">{{ fieldError('password') }}</span>
            </div>

            <div class="field">
                <label class="field-label" for="password_confirmation">{{ t('auth.register.password_confirm') }}</label>
                <input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="input"
                    autocomplete="new-password"
                    required
                    :placeholder="t('auth.password_confirm_placeholder')"
                />
            </div>

            <Button type="submit" variant="primary" :loading="loading" block>
                {{ loading ? t('auth.register.submitting') : t('auth.register.action') }}
            </Button>
        </form>

        <div class="auth-foot">
            <span>{{ t('auth.register.have_account') }}</span>
            <NuxtLink to="/auth/login" class="auth-link">{{ t('auth.register.signin') }}</NuxtLink>
        </div>
    </NuxtLayout>
</template>

