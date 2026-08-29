<script setup lang="ts">
/**
 * <LandingSiteFooter /> — product-aligned footer for the CV app.
 */
const { t } = useI18n();
const config = useRuntimeConfig();
const appName = config.public.appName as string;
const laravel = (config.public.laravelUrl as string).replace(/\/+$/, '');
const year = new Date().getFullYear();
const route = useRoute();

const logoSrc = `${laravel}/images/logo-icon.png`;
const onHome = computed(() => route.path === '/' || route.path === '');

/** Section anchors work from any page via `/#…`. */
const section = (id: string) => (onHome.value ? `#${id}` : `/#${id}`);
</script>

<template>
    <footer class="site-footer">
        <div class="site-footer__inner">
            <div class="site-footer__grid">
                <div class="site-footer__brand">
                    <NuxtLink to="/" class="site-footer__brand-mark">
                        <span class="site-footer__brand-icon" aria-hidden="true">
                            <img :src="logoSrc" alt="" width="18" height="18" />
                        </span>
                    </NuxtLink>
                    <p class="site-footer__brand-text">{{ t('landing.footer_about') }}</p>
                </div>

                <div class="site-footer__col">
                    <h4>{{ t('landing.footer_product') }}</h4>
                    <ul>
                        <li><a :href="section('platforms')">{{ t('landing.nav.platforms') }}</a></li>
                        <li><a :href="section('mockup')">{{ t('landing.nav.mockup') }}</a></li>
                        <li><a :href="section('pricing')">{{ t('landing.nav.pricing') }}</a></li>
                        <li><a :href="section('download')">{{ t('landing.nav.download') }}</a></li>
                    </ul>
                </div>

                <div class="site-footer__col">
                    <h4>{{ t('landing.footer_account') }}</h4>
                    <ul>
                        <li><NuxtLink to="/auth/login">{{ t('landing.nav.login') }}</NuxtLink></li>
                        <li><NuxtLink to="/auth/register">{{ t('landing.footer_create_account') }}</NuxtLink></li>
                        <li><NuxtLink to="/portal">{{ t('landing.nav.dashboard') }}</NuxtLink></li>
                    </ul>
                </div>

                <div class="site-footer__col">
                    <h4>{{ t('landing.footer_legal') }}</h4>
                    <ul>
                        <li><NuxtLink to="/privacy">{{ t('landing.footer_privacy') }}</NuxtLink></li>
                        <li><NuxtLink to="/terms">{{ t('landing.footer_terms') }}</NuxtLink></li>
                    </ul>
                </div>
            </div>

            <div class="site-footer__rule" />

            <div class="site-footer__bottom">
                <p class="site-footer__copy">
                    © {{ year }} {{ appName }}. {{ t('landing.footer_rights') }}
                </p>
                <div class="site-footer__legal">
                    <NuxtLink to="/terms">{{ t('landing.footer_terms') }}</NuxtLink>
                    <NuxtLink to="/privacy">{{ t('landing.footer_privacy') }}</NuxtLink>
                </div>
            </div>
        </div>
    </footer>
</template>

<style scoped>
.site-footer {
    width: 100%;
    background: var(--color-white);
    border-top: 1px solid var(--color-line);
}

.site-footer__inner {
    max-width: 76rem;
    margin-inline: auto;
    padding: clamp(2.5rem, 5vw, 3.5rem) 1.25rem 2rem;
}

@media (min-width: 768px) {
    .site-footer__inner {
        padding-inline: 2rem;
    }
}

@media (min-width: 1024px) {
    .site-footer__inner {
        padding-inline: 2.5rem;
    }
}

.site-footer__grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 2rem 2.5rem;
    margin-bottom: 2.5rem;
}

@media (min-width: 768px) {
    .site-footer__grid {
        grid-template-columns: 1.4fr 1fr 1fr 1fr;
        gap: 2rem;
    }
}

.site-footer__brand-mark {
    display: inline-flex;
    align-items: center;
    gap: 0.55rem;
    text-decoration: none;
    color: var(--color-ink);
    font-weight: 600;
    margin-bottom: 0.85rem;
}

.site-footer__brand-icon {
    display: inline-grid;
    place-items: center;
    width: 100px;
    height: 100px;
    overflow: hidden;
}

.site-footer__brand-icon img {
    display: block;
    width: 100px;
    height: 100px;
    object-fit: contain;
}

.site-footer__brand-text {
    margin: 0;
    max-width: 22rem;
    font-size: 0.92rem;
    line-height: 1.55;
    color: var(--color-ink-soft);
}

.site-footer__col h4 {
    margin: 0 0 1rem;
    font-size: 0.95rem;
    font-weight: 600;
    letter-spacing: -0.01em;
    color: var(--color-ink);
}

.site-footer__col ul {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 0.65rem;
}

.site-footer__col a {
    font-size: 0.9rem;
    color: var(--color-ink-soft);
    text-decoration: none;
    transition: color 0.15s ease;
}

.site-footer__col a:hover {
    color: var(--color-ink);
}

.site-footer__rule {
    height: 1px;
    background: var(--color-line);
    margin-bottom: 1.25rem;
}

.site-footer__bottom {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}

.site-footer__copy {
    margin: 0;
    font-size: 0.82rem;
    color: var(--color-muted);
}

.site-footer__legal {
    display: inline-flex;
    flex-wrap: wrap;
    gap: 1.25rem;
}

.site-footer__legal a {
    font-size: 0.82rem;
    color: var(--color-muted);
    text-decoration: underline;
    text-underline-offset: 3px;
    transition: color 0.15s ease;
}

.site-footer__legal a:hover {
    color: var(--color-ink);
}
</style>
