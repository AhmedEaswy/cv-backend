<script setup lang="ts">
/**
 * AppleAuthButton — Laravel Socialite Sign in with Apple hop.
 * After callback, google-handoff stores the Sanctum token and redirects.
 */
const { t } = useI18n()
const { start } = useAppleAuth()

function handleAppleLogin() {
    start()
}
</script>

<template>
    <button
        type="button"
        class="btn btn--secondary btn--block"
        @click="handleAppleLogin"
    >
        <Icon name="apple" :size="18" />
        {{ t('auth.login.continue_apple') }}
    </button>
</template>
