<script setup lang="ts">
/**
 * app.vue — root layout.
 *
 * Wires the dynamic <html lang>/dir, the brand font stack (display + body),
 * and mounts the global Toaster. Each page renders inside <NuxtPage />.
 *
 * Also reconciles sidebase Auth.js (Google) → Sanctum bearer token, matching
 * digi-pedia's synchronizeAuth() flow.
 */
const { locale, locales, t } = useI18n();
const config = useRuntimeConfig();
const appName = config.public.appName as string;
const route = useRoute();
const { status, signOut } = useAuth();
const api = useApi();
const { refresh } = useAuthSession();
const authSyncStarted = ref(false);

async function synchronizeAuth() {
    if (!import.meta.client || authSyncStarted.value) return;

    if (api.getToken()) {
        authSyncStarted.value = true;
        return;
    }

    if (status.value !== 'authenticated') return;

    authSyncStarted.value = true;

    try {
        const payload = await $fetch<{
            success?: boolean;
            result?: { token?: string; user?: unknown };
        }>('/api/auth/google-exchange', { method: 'POST' });

        const token = payload?.result?.token;
        if (payload?.success && token) {
            api.setToken(token);
            await refresh();
            const raw = typeof route.query.redirect === 'string' ? route.query.redirect : '';
            const next = raw.startsWith('/') && !raw.startsWith('//') ? raw : '/portal';
            if (route.path.startsWith('/auth/callback') || route.path.startsWith('/auth/login')) {
                await navigateTo(next);
            }
        }
    } catch (error: any) {
        const statusCode = error?.statusCode ?? error?.response?.status;
        if (statusCode === 401) {
            api.setToken(null);
            await signOut({ redirect: false });
        }
        authSyncStarted.value = false;
    }
}

watch(status, () => {
    void synchronizeAuth();
}, { immediate: true });

const currentLocale = computed(() =>
    (locales.value as Array<{ code: string; dir?: 'ltr' | 'rtl' }>).find(
        (l) => l.code === locale.value,
    ),
);

const htmlDir = computed(() => currentLocale.value?.dir ?? 'ltr');

const bodyFontHref =
    'https://fonts.googleapis.com/css2?family=Inter+Tight:wght@400;500;600;700&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap';

useHead({
    htmlAttrs: {
        lang: () => locale.value,
        dir: () => htmlDir.value,
        class: () => (locale.value === 'ar' ? 'locale-ar' : ''),
    },
    title: `${appName}`,
    meta: [
        { name: 'description', content: 'CV — build, share, and track your career documents.' },
        { name: 'theme-color', content: '#fafaf9' },
    ],
    link: computed(() => {
        const links: Array<Record<string, string>> = [
            {
                rel: 'preload',
                href: '/fonts/thmanyah/thmanyahsans-Regular.woff2',
                as: 'font',
                type: 'font/woff2',
                crossorigin: '',
            },
        ];

        if (locale.value !== 'ar') {
            links.push(
                { rel: 'preconnect', href: 'https://fonts.googleapis.com' },
                { rel: 'preconnect', href: 'https://fonts.gstatic.com', crossorigin: '' },
                { rel: 'stylesheet', href: bodyFontHref },
            );
        }

        return links;
    }),
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
