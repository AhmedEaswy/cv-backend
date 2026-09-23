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
        class="btn btn--secondary btn--block"
        @click="handleLinkedInLogin"
    >
        <Icon name="linkedin" :size="18" />
        {{ t('auth.login.continue_linkedin') }}
    </button>
</template>
