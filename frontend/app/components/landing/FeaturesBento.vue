<script setup lang="ts">
/**
 * <FeaturesBento />
 *
 * 3-column bento grid of feature cards. Each card shows an icon, an
 * uppercase eyebrow, a title, and a short text.
 */
const { t } = useI18n();

const features = [
    { key: 'templates', span: 'col-span-1 md:col-span-2 row-span-1' },
    { key: 'cover',     span: 'col-span-1 md:col-span-1 row-span-1' },
    { key: 'profile',   span: 'col-span-1 md:col-span-1 row-span-1' },
    { key: 'ats',       span: 'col-span-1 md:col-span-1 row-span-1' },
    { key: 'rtl',       span: 'col-span-1 md:col-span-1 row-span-1' },
    { key: 'security',  span: 'col-span-1 md:col-span-2 row-span-1' },
] as const;
</script>

<template>
    <section id="features" class="py-20 sm:py-28" style="background: var(--color-paper-2);">
        <div class="max-w-6xl mx-auto px-5 sm:px-8">
            <div class="max-w-2xl mb-12">
                <div class="text-xs font-semibold uppercase tracking-wider mb-3" style="color: var(--color-brand-primary);">
                    {{ t('landing.features.eyebrow') }}
                </div>
                <h2 class="font-display text-3xl sm:text-4xl font-bold text-[#130e21] mb-3 leading-tight">
                    {{ t('landing.features.title') }}
                </h2>
                <p class="text-[#4a4458] text-lg">
                    {{ t('landing.features.subtitle') }}
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 auto-rows-fr" style="grid-auto-flow: dense;">
                <article
                    v-for="f in features"
                    :key="f.key"
                    class="surface-card p-7"
                    :class="f.span"
                    style="display: flex; flex-direction: column; gap: 14px; min-height: 220px;"
                >
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span class="feature-icon" aria-hidden="true">
                            <IconsFeatureIcon :name="f.key" />
                        </span>
                        <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.12em; color: var(--color-brand-primary);">
                            {{ t(`landing.feature_${f.key}_eyebrow`) }}
                        </span>
                    </div>
                    <h3 class="font-display font-semibold text-[#130e21]" style="font-size: 1.25rem; letter-spacing: -0.01em; line-height: 1.25; margin: 0;">
                        {{ t(`landing.feature_${f.key}_title`) }}
                    </h3>
                    <p class="text-[#4a4458] leading-relaxed" style="margin: 0;">
                        {{ t(`landing.feature_${f.key}_text`) }}
                    </p>
                </article>
            </div>
        </div>
    </section>
</template>
