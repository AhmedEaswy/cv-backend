<script setup lang="ts">
/**
 * AppleAuthButton — Laravel Socialite Sign in with Apple hop.
 * After callback, google-handoff stores the Sanctum token and redirects.
 */
const { t } = useI18n()
const { start, enabled } = useAppleAuth()

function handleAppleLogin() {
    start()
}
</script>

<template>
    <button
        v-if="enabled"
        type="button"
        class="auth-social__btn"
        :aria-label="t('auth.login.continue_apple')"
        :title="t('auth.login.continue_apple')"
        @click="handleAppleLogin"
    >
        <SocialIcon provider="apple" :size="44" />
    </button>
</template>
