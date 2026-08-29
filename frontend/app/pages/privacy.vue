<script setup lang="ts">
/**
 * /privacy — Privacy Policy for the CV product.
 */
definePageMeta({ layout: 'legal' });

const { t } = useI18n();
const config = useRuntimeConfig();
const appName = config.public.appName as string;

useHead({
    title: () => `${t('legal.privacy.title')} — ${appName}`,
    meta: [{ name: 'description', content: () => t('legal.privacy.meta') }],
});

const sections = computed(() => [
    { title: t('legal.privacy.s1_title'), body: t('legal.privacy.s1_body') },
    { title: t('legal.privacy.s2_title'), body: t('legal.privacy.s2_body') },
    { title: t('legal.privacy.s3_title'), body: t('legal.privacy.s3_body') },
    { title: t('legal.privacy.s4_title'), body: t('legal.privacy.s4_body') },
    { title: t('legal.privacy.s5_title'), body: t('legal.privacy.s5_body') },
    { title: t('legal.privacy.s6_title'), body: t('legal.privacy.s6_body') },
    { title: t('legal.privacy.s7_title'), body: t('legal.privacy.s7_body') },
    { title: t('legal.privacy.s8_title'), body: t('legal.privacy.s8_body', { app: appName }) },
    { title: t('legal.privacy.s9_title'), body: t('legal.privacy.s9_body') },
]);
</script>

<template>
    <LegalDoc
        :title="t('legal.privacy.title')"
        :updated="t('legal.privacy.updated')"
    >
        <p>{{ t('legal.privacy.intro', { app: appName }) }}</p>

        <section v-for="(section, i) in sections" :key="i">
            <h2>{{ section.title }}</h2>
            <p>{{ section.body }}</p>
        </section>

        <section>
            <h2>{{ t('legal.privacy.s10_title') }}</h2>
            <p>{{ t('legal.privacy.s10_body') }}</p>
            <ul>
                <li>{{ t('legal.privacy.s10_item_1') }}</li>
                <li>{{ t('legal.privacy.s10_item_2') }}</li>
                <li>{{ t('legal.privacy.s10_item_3') }}</li>
                <li>{{ t('legal.privacy.s10_item_4') }}</li>
            </ul>
        </section>

        <section>
            <h2>{{ t('legal.privacy.s11_title') }}</h2>
            <p>{{ t('legal.privacy.s11_body', { app: appName }) }}</p>
        </section>
    </LegalDoc>
</template>
