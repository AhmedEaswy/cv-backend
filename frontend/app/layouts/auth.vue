<script setup lang="ts">
/**
 * <AuthLayout> — split-screen layout for the auth pages.
 * On the left (or top, on mobile): a grayscale marketing panel with
 * brand + tagline + bullet list. On the right: the actual form slot.
 *
 * Slots:
 *   - default — the form
 *   - aside   — replaces the marketing panel content
 *   - foot    — shown under the form (e.g. small print)
 */
defineProps<{
    title?: string;
    text?: string;
}>();

const config = useRuntimeConfig();
const appName = config.public.appName as string;

const laravel = (config.public.laravelUrl as string).replace(/\/+$/, '');

const logoSrc = `${laravel}/images/logo-horizontal-white.png`;
</script>

<template>
    <div class="auth-shell">
        <aside class="auth-aside">
            <div>
                <NuxtLink to="/" class="auth-aside__brand">
                    <img :src="logoSrc" alt="Logo" width="150" height="150" />
                </NuxtLink>
            </div>

            <div>
                <slot name="aside">
                    <h2 v-if="title">{{ title }}</h2>
                    <p v-if="text">{{ text }}</p>
                    <ul v-if="$slots.bullets">
                        <slot name="bullets" />
                    </ul>
                </slot>
            </div>

            <div class="auth-aside__foot">
                © {{ new Date().getFullYear() }} {{ appName }}
            </div>
        </aside>

        <section class="auth-form">
            <div class="auth-form__inner">
                <slot />
            </div>
        </section>
    </div>
</template>

<style scoped>
.auth-aside__brand {
    display: inline-flex;
    align-items: center;
    gap: 0.55rem;
    color: var(--color-paper);
    text-decoration: none;
    font-weight: 600;
    font-size: 0.95rem;
}
.auth-aside__mark {
    width: 30px;
    height: 30px;
    border-radius: 8px;
    background: var(--color-paper);
    color: var(--color-ink);
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.auth-aside__brand-text { line-height: 1; }
.auth-aside__foot {
    font-size: 0.78rem;
    color: rgba(250, 250, 249, 0.45);
}
</style>
