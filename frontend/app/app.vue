<script setup lang="ts">
/**
 * app.vue — root layout.
 *
 * Wires the dynamic <html lang>/dir, the brand font stack (display + body),
 * and mounts the global Toaster. Each page renders inside <NuxtPage />.
 */
const { locale, locales, t } = useI18n();
const config = useRuntimeConfig();
const appName = config.public.appName as string;

const currentLocale = computed(() =>
    (locales.value as Array<{ code: string; dir?: 'ltr' | 'rtl' }>).find(
        (l) => l.code === locale.value,
    ),
);

const htmlDir = computed(() => currentLocale.value?.dir ?? 'ltr');

const displayFontHref =
    'https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Inter+Tight:wght@400;500;600;700&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&family=Amiri:wght@400;700&display=swap';

useHead({
    htmlAttrs: {
        lang: () => locale.value,
        dir: () => htmlDir.value,
    },
    title: `${appName}`,
    meta: [
        { name: 'description', content: 'CV — build, share, and track your career documents.' },
        { name: 'theme-color', content: '#fafaf9' },
    ],
    link: [
        { rel: 'preconnect', href: 'https://fonts.googleapis.com' },
        { rel: 'preconnect', href: 'https://fonts.gstatic.com', crossorigin: '' },
        { rel: 'stylesheet', href: displayFontHref },
    ],
});
</script>

<template>
    <div>
        <NuxtLayout>
            <NuxtPage />
        </NuxtLayout>
        <Toaster />
    </div>
</template>
