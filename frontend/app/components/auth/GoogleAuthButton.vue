<script setup lang="ts">
/**
 * GoogleAuthButton — sidebase Auth.js Google sign-in (digi-pedia pattern).
 * After Auth.js succeeds, app.vue synchronizeAuth() exchanges for a Sanctum token.
 */
const { t } = useI18n()
const route = useRoute()
const { signIn, status } = useAuth()

const loading = computed(() => status.value === 'loading')

async function handleGoogleLogin() {
    const raw = typeof route.query.redirect === 'string' ? route.query.redirect : ''
    const next = raw.startsWith('/') && !raw.startsWith('//') ? raw : '/portal'
    const callbackUrl = `/auth/callback?redirect=${encodeURIComponent(next)}`
    try {
        await signIn('google', { callbackUrl })
    } catch (err) {
        console.error('Google sign-in failed', err)
    }
}
</script>

<template>
    <button
        type="button"
        class="btn btn--secondary btn--block"
        :disabled="loading"
        @click="handleGoogleLogin"
    >
        <Icon name="google" :size="18" />
        {{ t('auth.login.continue_google') }}
    </button>
</template>
