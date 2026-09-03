<script setup lang="ts">
/**
 * Legal / marketing pages layout — light header + shared footer.
 */
const { t } = useI18n();
const config = useRuntimeConfig();
const appName = config.public.appName as string;
const laravel = (config.public.laravelUrl as string).replace(/\/+$/, '');
const logoSrc = `${laravel}/images/logo-horizontal.png`;
</script>

<template>
    <div class="legal-shell">
        <header class="legal-header">
            <div class="legal-header__inner">
                <NuxtLink to="/" class="legal-header__brand" :aria-label="`${appName} — home`">
                    <img
                        :src="logoSrc"
                        :alt="appName"
                        class="legal-header__logo"
                        @error="(($event.target as HTMLImageElement).src = `${laravel}/images/logo-icon.png`)"
                    />
                </NuxtLink>
                <nav class="legal-header__nav" aria-label="Legal">
                    <NuxtLink to="/privacy">{{ t('landing.footer_privacy') }}</NuxtLink>
                    <NuxtLink to="/terms">{{ t('landing.footer_terms') }}</NuxtLink>
                    <NuxtLink to="/" class="legal-header__home">{{ t('legal.back_home') }}</NuxtLink>
                </nav>
            </div>
        </header>

        <main class="legal-main">
            <slot />
        </main>

        <LandingSiteFooter />
    </div>
</template>

