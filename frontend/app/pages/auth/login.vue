<script setup lang="ts">
/**
 * /auth/login
 */
const { t } = useI18n();
const config = useRuntimeConfig();
const laravel = (config.public.laravelUrl as string).replace(/\/+$/, '');
const { login, loading, fieldError, generalError } = useAuthSession();

const form = reactive({
    email: '',
    password: '',
    remember: false,
});

const showPwd = ref(false);
const onSubmit = () => login({ ...form });
</script>

<template>
    <NuxtLayout name="auth">
        <template #aside>
            <h2>{{ t('auth.login.aside.title') }}</h2>
            <p>{{ t('auth.login.aside.text') }}</p>
            <ul>
                <li>
                    <Icon name="check" :size="16" />
                    <span>{{ t('auth.login.aside.bullet_1') }}</span>
                </li>
                <li>
                    <Icon name="check" :size="16" />
                    <span>{{ t('auth.login.aside.bullet_2') }}</span>
                </li>
                <li>
                    <Icon name="check" :size="16" />
                    <span>{{ t('auth.login.aside.bullet_3') }}</span>
                </li>
            </ul>
        </template>

        <h1 class="auth-form__title">{{ t('auth.login.title') }}</h1>
        <p class="auth-form__subtitle">{{ t('auth.login.subtitle') }}</p>

        <Alert v-if="generalError" variant="error">{{ generalError }}</Alert>

        <a :href="laravel + '/auth/google/redirect'" class="btn btn--secondary btn--block">
            <Icon name="google" :size="18" />
            {{ t('auth.login.continue_google') }}
        </a>

        <div class="auth-divider">{{ t('auth.or') }}</div>

        <form @submit.prevent="onSubmit" novalidate>
            <div class="field">
                <label class="field-label" for="email">{{ t('auth.login.email') }}</label>
                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="input"
                    autocomplete="email"
                    inputmode="email"
                    required
                    :aria-invalid="!!fieldError('email')"
                />
                <span v-if="fieldError('email')" class="field-error">{{ fieldError('email') }}</span>
            </div>

            <div class="field">
                <label class="field-label" for="password">{{ t('auth.login.password') }}</label>
                <div class="pwd-wrap">
                    <input
                        id="password"
                        v-model="form.password"
                        :type="showPwd ? 'text' : 'password'"
                        class="input"
                        autocomplete="current-password"
                        required
                        :aria-invalid="!!fieldError('password')"
                    />
                    <button type="button" class="pwd-toggle" @click="showPwd = !showPwd" :aria-label="showPwd ? 'Hide password' : 'Show password'">
                        <Icon :name="showPwd ? 'x' : 'eye'" :size="16" />
                    </button>
                </div>
                <span v-if="fieldError('password')" class="field-error">{{ fieldError('password') }}</span>
            </div>

            <label class="checkbox checkbox--remember">
                <input v-model="form.remember" type="checkbox" name="remember" />
                <span>{{ t('auth.login.remember') }}</span>
            </label>

            <Button type="submit" variant="primary" :loading="loading" block>
                {{ loading ? t('auth.login.submitting') : t('auth.login.action') }}
            </Button>
        </form>

        <div class="auth-foot">
            <NuxtLink to="/auth/forgot-password" class="auth-link">{{ t('auth.login.forgot') }}</NuxtLink>
            <span class="auth-foot__sep" aria-hidden="true">·</span>
            <NuxtLink to="/auth/register" class="auth-link">{{ t('auth.login.no_account') }} {{ t('auth.login.signup') }}</NuxtLink>
        </div>
    </NuxtLayout>
</template>

