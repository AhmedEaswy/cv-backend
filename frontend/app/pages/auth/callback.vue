<script setup lang="ts">
/**
 * /auth/callback — public handoff after Auth.js Google OIDC.
 * app.vue synchronizeAuth() exchanges for Sanctum and navigates onward.
 */
const { t } = useI18n()
const route = useRoute()
const { status } = useAuth()
const api = useApi()

const redirectTo = computed(() => {
    const raw = typeof route.query.redirect === 'string' ? route.query.redirect : ''
    return raw.startsWith('/') && !raw.startsWith('//') ? raw : '/portal'
})

onMounted(async () => {
    // Already have a Sanctum token (e.g. refresh mid-flow).
    if (api.getToken()) {
        await navigateTo(redirectTo.value)
        return
    }

    // Wait briefly for Auth.js session + app.vue exchange.
    for (let i = 0; i < 40; i++) {
        if (api.getToken()) {
            await navigateTo(redirectTo.value)
            return
        }
        if (status.value === 'unauthenticated') {
            await navigateTo({
                path: '/auth/login',
                query: { error: 'social', redirect: redirectTo.value },
            })
            return
        }
        await new Promise((r) => setTimeout(r, 150))
    }

    await navigateTo({
        path: '/auth/login',
        query: { error: 'social', redirect: redirectTo.value },
    })
})
</script>

<template>
    <div class="auth-callback">
        <p>{{ t('auth.login.submitting') }}</p>
    </div>
</template>

<style scoped>
.auth-callback {
    min-height: 50vh;
    display: grid;
    place-items: center;
    color: #64748b;
}
</style>
