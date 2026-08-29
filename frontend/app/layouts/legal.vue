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

<style scoped>
.legal-shell {
    min-height: 100vh;
    background: var(--color-paper);
    color: var(--color-ink);
}

.legal-header {
    position: sticky;
    top: 0;
    z-index: 20;
    background: rgba(250, 250, 249, 0.9);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border-bottom: 1px solid var(--color-line);
}

.legal-header__inner {
    max-width: 48rem;
    margin-inline: auto;
    padding: 0.85rem 1.25rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}

.legal-header__brand {
    display: inline-flex;
    align-items: center;
}

.legal-header__logo {
    height: 1.6rem;
    width: auto;
}

.legal-header__nav {
    display: flex;
    align-items: center;
    gap: 1rem;
    font-size: 0.85rem;
}

.legal-header__nav a {
    color: var(--color-ink-soft);
    text-decoration: none;
}

.legal-header__nav a:hover,
.legal-header__nav a.router-link-active {
    color: var(--color-ink);
}

.legal-header__home {
    font-weight: 500;
}

.legal-main {
    max-width: 48rem;
    margin-inline: auto;
    padding: 2.5rem 1.25rem 4rem;
}
</style>
