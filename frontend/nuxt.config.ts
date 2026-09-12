// https://nuxt.com/docs/api/configuration/nuxt-config
import tailwindcss from '@tailwindcss/vite';
import tsconfigPaths from 'vite-tsconfig-paths';

export default defineNuxtConfig({
    compatibilityDate: '2025-08-01',
    devtools: { enabled: true },

    // Tell Nuxt the `app/` directory is the source root. This is the v4
    // convention; doing it now means `~` and `@` resolve under app/ —
    // e.g. `~/components/Foo.vue` → `app/components/Foo.vue`.
    srcDir: 'app/',
    // With srcDir=app/, Nuxt defaults serverDir to app/server. Keep API
    // handlers at frontend/server (Auth.js, google-exchange, storage).
    serverDir: 'server',

    // Static assets (fonts, etc.) live in app/public/ with srcDir set.
    dir: {
        public: 'public',
    },

    // SSR is on so the landing gets full SEO + meta tags server-rendered.
    ssr: true,

    modules: [
        '@nuxtjs/i18n',
        '@pinia/nuxt',
        '@vueuse/nuxt',
        '@sidebase/nuxt-auth',
    ],

    // Sidebase Auth.js (Google OIDC only). Sanctum bearer token remains the app session.
    // baseURL MUST include /api/auth or sidebase recurses on /session (hangs SSR / smoke).
    auth: {
        isEnabled: true,
        baseURL: process.env.NUXT_AUTH_ORIGIN || 'https://cv.test/api/auth',
        originEnvKey: 'NUXT_AUTH_ORIGIN',
        // Auth.js is only used for the Google hop; portal auth is Sanctum/localStorage.
        // Skipping SSR session fetch avoids /session recursion when curling :3001 in deploy.
        disableServerSideAuth: true,
        provider: {
            type: 'authjs',
            trustHost: true,
            defaultProvider: 'google',
            addDefaultCallbackUrl: true,
        },
        globalAppMiddleware: false,
    },

    plugins: [
        '~/plugins/i18n-messages.ts',
    ],

    css: ['@/assets/css/main.css'],

    // Auto-import all components flat — `components/landing/SiteHeader.vue` is
    // available as `<LandingSiteHeader />` (the directory name is the prefix).
    // `components/ui/*.vue` and `components/icons/*.vue` are imported without
    // a directory prefix so the public API stays clean: <Button>, <Modal>, <Icon>.
    components: [
        { path: '~/components/ui', pathPrefix: false },
        { path: '~/components/icons', pathPrefix: false },
        { path: '~/components/portal', pathPrefix: false },
        { path: '~/components', pathPrefix: true },
    ],

    app: {
        head: {
            htmlAttrs: { lang: 'en', dir: 'ltr' },
            meta: [
                { charset: 'utf-8' },
                { name: 'viewport', content: 'width=device-width, initial-scale=1' },
                { name: 'description', content: '' },
            ],
            link: [
                { rel: 'icon', type: 'image/png', href: '/images/cv-logo.png' },
            ],
        },
    },

    // Tailwind 4 via @tailwindcss/vite
    devServer: {
        host: '127.0.0.1',
        port: 3000,
    },

    vite: {
        plugins: [tailwindcss(), tsconfigPaths()],
        server: {
            // Allow Laragon proxy (https://cv.test → :3000).
            allowedHosts: ['cv.test', '.cv.test', 'localhost', '127.0.0.1'],
            // HMR through Laragon/nginx at https://cv.test
            hmr: {
                protocol: 'wss',
                host: 'cv.test',
                clientPort: 443,
            },
        },
    },

    // Public runtime config — these end up in the client bundle and are safe
    // to expose. The Laravel base URL is the only thing Nuxt needs to call.
    runtimeConfig: {
        // Used by sidebase originEnvKey / getToken at runtime (NUXT_AUTH_ORIGIN overrides).
        authOrigin: process.env.NUXT_AUTH_ORIGIN || 'https://cv.test/api/auth',
        authSecret: process.env.NUXT_AUTH_SECRET || process.env.AUTH_SECRET || '',
        googleClientId: process.env.NUXT_GOOGLE_CLIENT_ID || process.env.GOOGLE_CLIENT_ID || '',
        private: {
            googleClientSecret:
                process.env.NUXT_GOOGLE_CLIENT_SECRET || process.env.GOOGLE_CLIENT_SECRET || '',
        },
        public: {
            laravelUrl: process.env.NUXT_PUBLIC_LARAVEL_URL || 'http://localhost:8000',
            appName: process.env.NUXT_PUBLIC_APP_NAME || 'CV',
            appStoreUrl: process.env.NUXT_PUBLIC_APP_STORE_URL || '',
            playStoreUrl: process.env.NUXT_PUBLIC_PLAY_STORE_URL || '',
            apiPrefix: '/api/v1',
            googleClientId: process.env.NUXT_GOOGLE_CLIENT_ID || process.env.GOOGLE_CLIENT_ID || '',
        },
    },

    // i18n — single source of truth is Laravel's resources/lang/*.json
    // (synced to frontend/locales/ by scripts/sync-locales.mjs).
    //
    // We use `vueI18n` to pass messages directly (the locale file loader
    // has path-resolution issues on Windows). The plugin
    // app/plugins/i18n-messages.ts then merges the messages into the
    // active i18n instance for both server and client.
    i18n: {
        strategy: 'no_prefix',
        defaultLocale: 'en',
        // Keep vue-i18n config at frontend root (not under i18n/).
        restructureDir: false,
        // Locales are flat JSON keys ("auth.login.title"); flatJson avoids CompileError 10.
        vueI18n: 'i18n.config.ts',
        // No langDir/file: messages are merged by app/plugins/i18n-messages.ts.
        locales: [
            { code: 'en', language: 'en-US', name: 'English',  dir: 'ltr' },
            { code: 'ar', language: 'ar',    name: 'العربية',  dir: 'rtl' },
            { code: 'tr', language: 'tr-TR', name: 'Türkçe',   dir: 'ltr' },
            { code: 'es', language: 'es-ES', name: 'Español',  dir: 'ltr' },
            { code: 'fr', language: 'fr-FR', name: 'Français', dir: 'ltr' },
            { code: 'de', language: 'de-DE', name: 'Deutsch',  dir: 'ltr' },
            { code: 'ur', language: 'ur',    name: 'اردو',      dir: 'rtl' },
        ],
        detectBrowserLanguage: {
            useCookie: true,
            cookieKey: 'i18n_redirected',
            redirectOn: 'root',
        },
        bundle: {
            optimizeTranslationDirective: false,
        },
    },

    experimental: {
        payloadExtraction: true,
    },

    nitro: {
        routeRules: {
            '/**': { headers: { 'X-Frame-Options': 'SAMEORIGIN' } },
            // Document HTML should revalidate so clients never keep a stale
            // chunk manifest that points at deleted /_nuxt hashes.
            '/': { headers: { 'Cache-Control': 'no-cache' } },
            '/templates': { headers: { 'Cache-Control': 'no-cache' } },
            '/_nuxt/**': {
                headers: {
                    'Cache-Control': 'public, max-age=31536000, immutable',
                },
            },
        },
    },

    typescript: {
        strict: true,
        typeCheck: false,
    },
});
