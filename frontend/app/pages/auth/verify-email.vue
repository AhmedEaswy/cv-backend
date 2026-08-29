<script setup lang="ts">
/**
 * /auth/verify-email — sent from registration.
 * The link in the email lands on the Laravel route which marks the
 * user verified and redirects them back here. This page just shows a
 * confirmation and a CTA to the portal.
 */
const { t } = useI18n();
const { user, logout } = useAuthSession();
</script>

<template>
    <NuxtLayout name="auth">
        <template #aside>
            <h2>{{ t('auth.verify.title') }}</h2>
            <p>{{ t('auth.verify.subtitle') }}</p>
        </template>

        <div class="verify">
            <span class="verify__icon">
                <Icon name="mail" :size="28" />
            </span>
            <h1 class="auth-form__title">{{ t('auth.verify.title') }}</h1>
            <p class="auth-form__subtitle">{{ t('auth.verify.subtitle') }}</p>

            <div class="verify__cta">
                <NuxtLink to="/portal" class="btn btn--primary btn--block">
                    {{ t('auth.signin') }}
                </NuxtLink>
                <Button variant="ghost" block @click="logout">
                    {{ t('auth.verify.signout') }}
                </Button>
            </div>
        </div>
    </NuxtLayout>
</template>

<style scoped>
.verify { text-align: center; }
.verify__icon {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background: var(--color-paper-2);
    color: var(--color-ink);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1.5rem;
}
.verify__cta { display: flex; flex-direction: column; gap: 0.5rem; margin-top: 1.5rem; }
</style>
