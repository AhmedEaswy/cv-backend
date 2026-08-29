<script setup lang="ts">
/**
 * /terms — Terms of Service for the CV product.
 */
definePageMeta({ layout: 'legal' });

const { t } = useI18n();
const config = useRuntimeConfig();
const appName = config.public.appName as string;

useHead({
    title: () => `${t('legal.terms.title')} — ${appName}`,
    meta: [{ name: 'description', content: () => t('legal.terms.meta') }],
});

const sections = computed(() => {
    const app = { app: appName };
    return [
        { title: t('legal.terms.s1_title'), body: t('legal.terms.s1_body', app) },
        { title: t('legal.terms.s2_title'), body: t('legal.terms.s2_body') },
        { title: t('legal.terms.s3_title'), body: t('legal.terms.s3_body') },
        { title: t('legal.terms.s4_title'), body: t('legal.terms.s4_body') },
        { title: t('legal.terms.s5_title'), body: t('legal.terms.s5_body') },
        { title: t('legal.terms.s6_title'), body: t('legal.terms.s6_body', app) },
        { title: t('legal.terms.s7_title'), body: t('legal.terms.s7_body', app) },
        { title: t('legal.terms.s8_title'), body: t('legal.terms.s8_body') },
        { title: t('legal.terms.s9_title'), body: t('legal.terms.s9_body') },
        { title: t('legal.terms.s10_title'), body: t('legal.terms.s10_body', app) },
    ];
});
</script>

<template>
    <LegalDoc
        :title="t('legal.terms.title')"
        :updated="t('legal.terms.updated')"
    >
        <p>{{ t('legal.terms.intro', { app: appName }) }}</p>

        <section v-for="(section, i) in sections" :key="i">
            <h2>{{ section.title }}</h2>
            <p>{{ section.body }}</p>
        </section>

        <section>
            <h2>{{ t('legal.terms.s11_title') }}</h2>
            <p>{{ t('legal.terms.s11_body', { app: appName }) }}</p>
            <ul>
                <li>{{ t('legal.terms.s11_item_1') }}</li>
                <li>{{ t('legal.terms.s11_item_2') }}</li>
                <li>{{ t('legal.terms.s11_item_3') }}</li>
            </ul>
        </section>

        <section>
            <h2>{{ t('legal.terms.s12_title') }}</h2>
            <p>{{ t('legal.terms.s12_body', { app: appName }) }}</p>
        </section>
    </LegalDoc>
</template>
