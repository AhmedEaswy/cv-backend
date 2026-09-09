<script setup lang="ts">
/**
 * pages/index.vue — the landing page.
 *
 * Composes the new grayscale design sections in the order that mirrors
 * the reference image. Each section is a self-contained component.
 *
 * SSR is enabled globally in nuxt.config.ts, so this page is server-
 * rendered for crawlers. Auth state is fetched in useAuthUser(), which
 * forwards the request cookie on the server.
 */
const { t } = useI18n();
const config = useRuntimeConfig();
const appName = config.public.appName as string;

useHead({
    title: () => `${t('landing.meta_title')} — ${appName}`,
    meta: [
        { name: 'description', content: () => t('landing.meta_description') },
        { property: 'og:title', content: () => `${t('landing.meta_title')} — ${appName}` },
        { property: 'og:description', content: () => t('landing.meta_description') },
        { property: 'og:type', content: 'website' },
    ],
});
</script>

<template>
    <div>
        <LandingSiteHeader />
        <main>
            <LandingHeroSection />
            <LandingPlatformsSection />
            <LandingAiConnectSection />
            <LandingAiConnectModal />

            <!-- <LandingMarqueeStrip /> -->
            <LandingMockupSection />
            <!-- <LandingTemplatesSection /> -->
             
            <LandingPricingSection />
            <div class="landing-closing">
                <div class="landing-closing__shell">
                    <LandingDownloadSection />
                </div>
            </div>
        </main>
        <LandingSiteFooter />
    </div>
</template>

