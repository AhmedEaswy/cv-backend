<script setup lang="ts">
/**
 * <LandingHeader /> — the new grayscale top bar.
 * Sticky, blurred, minimal. Matches the reference (centered nav, pill
 * right-side actions).
 */
const { t } = useI18n();
const config = useRuntimeConfig();
const appName = config.public.appName as string;
const laravel = (config.public.laravelUrl as string).replace(/\/+$/, '');

const { user } = await useAuth();
const route = useRoute();
const onLanding = computed(() => route.path === '/' || route.path === '');
</script>

<template>
    <header class="landing-header">
        <div class="container-narrow landing-header__inner">
            <NuxtLink to="/" class="landing-brand" :aria-label="`${appName} — home`">
                <span class="landing-brand__mark" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" aria-hidden="true">
                        <path d="M5 3.5C5 2.67 5.67 2 6.5 2H17c.55 0 1 .45 1 1v2.5h-2.25V4.5H8.25V6H6V3.5z" />
                        <path d="M5 6.5h14a1 1 0 0 1 1 1V20a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7.5a1 1 0 0 1 1-1zM7 11h10v1.5H7V11zm0 3h7v1.5H7V14zm0 3h10v1.5H7V17z" />
                    </svg>
                </span>
                <span class="landing-brand__text">{{ appName }}</span>
            </NuxtLink>

            <nav class="landing-nav" aria-label="Primary">
                <a href="#templates">{{ t('landing.nav.templates') }}</a>
                <a href="#features">{{ t('landing.nav.features') }}</a>
                <a href="#pricing">{{ t('landing.nav.pricing') }}</a>
                <a href="#download">{{ t('landing.section_download_title') }}</a>
            </nav>

            <div class="landing-header__actions">
                <LangSwitcher />
                <template v-if="user">
                    <NuxtLink :to="laravel + '/portal'" class="btn btn--secondary btn--sm">
                        {{ t('landing.nav.dashboard') }}
                    </NuxtLink>
                </template>
                <template v-else>
                    <NuxtLink :to="laravel + '/login'" class="btn btn--ghost btn--sm">
                        {{ t('landing.nav.login') }}
                    </NuxtLink>
                    <NuxtLink to="#pricing" class="btn btn--primary btn--sm">
                        {{ t('landing.nav.cta') }}
                    </NuxtLink>
                </template>
            </div>
        </div>
    </header>
</template>

