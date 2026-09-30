<script setup lang="ts">
/**
 * LinkedInAuthButton — Laravel Socialite OpenID hop.
 * After callback, google-handoff stores the Sanctum token and redirects.
 */
const { t } = useI18n()
const { start, enabled } = useLinkedInAuth()

function handleLinkedInLogin() {
    start({ intent: 'login' })
}
</script>

<template>
    <button
        v-if="enabled"
        type="button"
        class="auth-social__btn"
        :aria-label="t('auth.login.continue_linkedin')"
        :title="t('auth.login.continue_linkedin')"
        @click="handleLinkedInLogin"
    >
        <SocialIcon provider="linkedin" :size="44" />
    </button>
</template>
