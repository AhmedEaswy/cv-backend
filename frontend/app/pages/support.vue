<script setup lang="ts">
/**
 * /support — public help center (public help-article routes).
 */
definePageMeta({ layout: 'legal' });

const { t } = useI18n();
const config = useRuntimeConfig();
const appName = config.public.appName as string;

useHead({
    title: () => `${t('support.meta.title')} — ${appName}`,
    meta: [{ name: 'description', content: () => t('support.meta.description') }],
});
</script>

<template>
    <div class="support-page">
        <header class="support-page__hero">
            <p class="support-page__eyebrow">{{ t('support.hero.eyebrow') }}</p>
            <h1>{{ t('support.hero.title') }}</h1>
            <p class="support-page__lede">{{ t('support.hero.lede') }}</p>
            <nav class="support-page__jump" :aria-label="t('support.jump_nav')">
                <a href="#support-faq">{{ t('support.jump.faq') }}</a>
                <a href="#support-guides">{{ t('support.jump.guides') }}</a>
                <a href="#support-features">{{ t('support.jump.features') }}</a>
                <a href="#support-contact">{{ t('support.jump.contact') }}</a>
            </nav>
        </header>

        <SupportHub scope="public" />
    </div>
</template>
