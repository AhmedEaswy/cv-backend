<script setup lang="ts">
/**
 * Shared prose shell for Privacy / Terms pages.
 */
defineProps<{
    title: string;
    updated: string;
}>();
</script>

<template>
    <article class="legal-doc">
        <header class="legal-doc__head">
            <h1 class="legal-doc__title">{{ title }}</h1>
            <p class="legal-doc__updated">{{ updated }}</p>
        </header>
        <div class="legal-doc__body">
            <slot />
        </div>
    </article>
</template>

<style scoped>
.legal-doc__head {
    margin-bottom: 2.25rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid var(--color-line);
}

.legal-doc__title {
    margin: 0 0 0.65rem;
    font-family: var(--font-display);
    font-size: clamp(2rem, 4vw, 2.75rem);
    font-weight: 400;
    letter-spacing: -0.03em;
    line-height: 1.15;
}

.legal-doc__updated {
    margin: 0;
    font-size: 0.9rem;
    color: var(--color-muted);
}

.legal-doc__body {
    display: flex;
    flex-direction: column;
    gap: 1.75rem;
}

.legal-doc__body :deep(h2) {
    margin: 0.5rem 0 0;
    font-size: 1.15rem;
    font-weight: 600;
    letter-spacing: -0.01em;
}

.legal-doc__body :deep(p),
.legal-doc__body :deep(li) {
    margin: 0;
    font-size: 0.98rem;
    line-height: 1.65;
    color: var(--color-ink-soft);
}

.legal-doc__body :deep(ul) {
    margin: 0;
    padding-inline-start: 1.25rem;
    display: flex;
    flex-direction: column;
    gap: 0.45rem;
}

.legal-doc__body :deep(a) {
    color: var(--color-ink);
    text-underline-offset: 3px;
}
</style>
