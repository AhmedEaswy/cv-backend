<script setup lang="ts">
/**
 * <FeaturesBento />
 *
 * 3-column bento grid of feature cards.
 */
const { t } = useI18n();

const features = [
    { key: 'templates', span: 'bento-grid__cell--wide-md' },
    { key: 'cover',     span: '' },
    { key: 'profile',   span: '' },
    { key: 'ats',       span: '' },
    { key: 'rtl',       span: '' },
    { key: 'security',  span: 'bento-grid__cell--wide-md' },
] as const;
</script>

<template>
    <section id="features" class="bento-section">
        <div class="feature-section__container bento-section__inner">
            <div class="bento-section__head">
                <div class="feature-section__eyebrow">
                    {{ t('landing.features.eyebrow') }}
                </div>
                <h2 class="feature-section__title">
                    {{ t('landing.features.title') }}
                </h2>
                <p class="feature-section__subtitle">
                    {{ t('landing.features.subtitle') }}
                </p>
            </div>

            <div class="bento-grid">
                <article
                    v-for="f in features"
                    :key="f.key"
                    class="surface-card bento-grid__cell"
                    :class="f.span"
                >
                    <div class="bento-grid__cell-head">
                        <span class="feature-icon" aria-hidden="true">
                            <IconsFeatureIcon :name="f.key" />
                        </span>
                        <span class="bento-grid__eyebrow">
                            {{ t(`landing.feature_${f.key}_eyebrow`) }}
                        </span>
                    </div>
                    <h3 class="bento-grid__title">
                        {{ t(`landing.feature_${f.key}_title`) }}
                    </h3>
                    <p class="bento-grid__text">
                        {{ t(`landing.feature_${f.key}_text`) }}
                    </p>
                </article>
            </div>
        </div>
    </section>
</template>
