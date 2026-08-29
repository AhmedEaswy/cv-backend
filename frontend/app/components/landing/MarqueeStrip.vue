<script setup lang="ts">
/**
 * <LandingMarqueeStrip /> — the "smart workspace" trusted-by strip.
 * A grayscale infinite-marquee of small data cards (devices, languages,
 * metrics). Pure visual texture between the hero and the notes section.
 */
const { t } = useI18n();

interface Item { label: string; meta: string; }
const items: Item[] = [
    { label: 'iPhone 15 Pro',       meta: 'iOS · 18' },
    { label: 'Pixel 8',             meta: 'Android · 14' },
    { label: 'MacBook Air',         meta: 'macOS · 14' },
    { label: 'Windows 11',          meta: 'x64 · 23H2' },
    { label: 'iPad Air',            meta: 'iPadOS · 17' },
    { label: 'Web · Chrome',        meta: 'v 121' },
    { label: 'Web · Safari',        meta: 'v 17' },
    { label: 'Web · Firefox',       meta: 'v 122' },
];
const track = computed(() => [...items, ...items]);
</script>

<template>
    <section class="marquee" :aria-label="t('landing.section_features_subtitle')">
        <div class="marquee-track">
            <div v-for="(it, i) in track" :key="i" class="marquee-item">
                <div class="marquee-item__label">{{ it.label }}</div>
                <div class="marquee-item__meta">{{ it.meta }}</div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.marquee-item {
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 0.4rem;
}
.marquee-item__label {
    font-family: var(--font-display);
    font-size: 1.15rem;
    line-height: 1;
    color: var(--color-ink);
    letter-spacing: -0.015em;
}
.marquee-item__meta {
    font-size: 0.75rem;
    color: var(--color-muted);
    letter-spacing: 0.04em;
    text-transform: uppercase;
}
</style>
