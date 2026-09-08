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
                        <li><NuxtLink to="/templates">{{ t('landing.nav.templates') }}</NuxtLink></li>
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

