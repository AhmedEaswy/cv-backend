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
</script>

<template>
    <div class="auth-shell">
        <aside class="auth-aside">
            <div>
                <NuxtLink to="/" class="auth-aside__brand">
                    <span class="auth-aside__mark" aria-hidden="true">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M5 3.5C5 2.67 5.67 2 6.5 2H17c.55 0 1 .45 1 1v2.5h-2.25V4.5H8.25V6H6V3.5z" />
                            <path d="M5 6.5h14a1 1 0 0 1 1 1V20a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7.5a1 1 0 0 1 1-1zM7 11h10v1.5H7V11zm0 3h7v1.5H7V14zm0 3h10v1.5H7V17z" />
                        </svg>
                    </span>
                    <span class="auth-aside__brand-text">Scribblit</span>
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
                © {{ new Date().getFullYear() }} Scribblit
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
