<script setup lang="ts">
/**
 * /auth/verify-email — enter the 6-digit OTP sent after registration.
 */
const { t } = useI18n();
const route = useRoute();
const { verifyEmail, resendVerification, loading, fieldError, generalError } = useAuthSession();

const email = computed(() => {
    const q = route.query.email;
    return typeof q === 'string' ? q : '';
});

const form = reactive({ code: '' });
const resent = ref(false);
const resendCooldown = ref(0);
let cooldownTimer: ReturnType<typeof setInterval> | null = null;

function startCooldown(seconds = 60) {
    resendCooldown.value = seconds;
    if (cooldownTimer) clearInterval(cooldownTimer);
    cooldownTimer = setInterval(() => {
        resendCooldown.value = Math.max(0, resendCooldown.value - 1);
        if (resendCooldown.value <= 0 && cooldownTimer) {
            clearInterval(cooldownTimer);
            cooldownTimer = null;
        }
    }, 1000);
}

onBeforeUnmount(() => {
    if (cooldownTimer) clearInterval(cooldownTimer);
});

async function onSubmit() {
    if (!email.value || loading.value || form.code.length !== 6) return;
    await verifyEmail({ email: email.value, code: form.code.trim() });
}

async function onResend() {
    if (!email.value || resendCooldown.value > 0) return;
    const r = await resendVerification({ email: email.value });
    if (r.ok) {
        resent.value = true;
        startCooldown(60);
    }
}

startCooldown(60);
</script>

<template>
    <NuxtLayout name="auth">
        <template #aside>
            <h2>{{ t('auth.verify.aside.title') }}</h2>
            <p>{{ t('auth.verify.aside.text') }}</p>
        </template>

        <h1 class="auth-form__title">{{ t('auth.verify.title') }}</h1>
        <p class="auth-form__subtitle">
            {{ email ? t('auth.verify.subtitle_with_email', { email }) : t('auth.verify.subtitle') }}
        </p>

        <Alert v-if="resent" variant="success">{{ t('auth.verify.sent') }}</Alert>
        <Alert v-else-if="generalError" variant="error">{{ generalError }}</Alert>
        <Alert v-if="!email" variant="error">{{ t('auth.verify.missing_email') }}</Alert>

        <form v-if="email" @submit.prevent="onSubmit" novalidate>
            <div class="field">
                <label class="field-label" for="code">{{ t('auth.verify.code') }}</label>
                <InputOtp
                    id="code"
                    v-model="form.code"
                    :length="6"
                    autofocus
                    autocomplete="one-time-code"
                    :invalid="!!fieldError('code')"
                    @complete="onSubmit"
                />
                <span v-if="fieldError('code')" class="field-error">{{ fieldError('code') }}</span>
            </div>

            <Button type="submit" variant="primary" :loading="loading" block>
                {{ loading ? t('auth.verify.submitting') : t('auth.verify.action') }}
            </Button>
        </form>

        <div class="auth-foot">
            <Button
                v-if="email"
                variant="ghost"
                :disabled="resendCooldown > 0 || loading"
                @click="onResend"
            >
                {{
                    resendCooldown > 0
                        ? t('auth.verify.resend_in', { seconds: resendCooldown })
                        : t('auth.verify.resend')
                }}
            </Button>
            <NuxtLink to="/auth/login" class="auth-link">
                <Icon name="arrow-left" :size="12" /> {{ t('auth.signin') }}
            </NuxtLink>
        </div>
    </NuxtLayout>
</template>
