<script setup lang="ts">
/**
 * <LandingFeatures /> — "Smarter Notes…" section.
 * Three feature cards with icons (black filled + outline variant for the
 * third) on the right, copy on the left.
 */
const { t } = useI18n();

const cards = computed(() => ([
    {
        title: t('landing.section_features_card_1_title'),
        text:  t('landing.section_features_card_1_text'),
        icon:  'sparkles',
        variant: 'filled',
    },
    {
        title: t('landing.section_features_card_2_title'),
        text:  t('landing.section_features_card_2_text'),
        icon:  'zap',
        variant: 'filled',
    },
    {
        title: t('landing.section_features_card_3_title'),
        text:  t('landing.section_features_card_3_text'),
        icon:  'globe',
        variant: 'outline',
    },
]));
</script>

<template>
    <section id="features" class="section !pb-0">
        <div class="container-narrow">
            <div class="features-head">
                <h2 class="display-2">{{ t('landing.section_features_title') }}</h2>
                <p class="lede">{{ t('landing.section_features_subtitle') }}</p>
            </div>

            <div class="features-grid">
                <div v-for="(c, i) in cards" :key="i" class="feature-card">
                    <div :class="['bento__icon', c.variant === 'outline' && 'bento__icon--outline']">
                        <Icon :name="c.icon" :size="20" />
                    </div>
                    <h3 class="display-3 feature-card__title">{{ c.title }}</h3>
                    <p class="feature-card__text">{{ c.text }}</p>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.features-head { text-align: center; margin-bottom: 3.5rem; max-width: 40rem; margin-inline: auto; }
.features-head .display-2 { margin: 0 0 1rem; }
.features-head .lede { margin: 0 auto; }

.features-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.25rem;
}
@media (min-width: 768px) { .features-grid { grid-template-columns: repeat(2, 1fr); } }
@media (min-width: 1024px) { .features-grid { grid-template-columns: repeat(3, 1fr); } }

.feature-card {
    background: var(--color-white);
    border: 1px solid var(--color-line);
    border-radius: var(--radius-xl);
    padding: 1.75rem;
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
    transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
    min-height: 240px;
}
.feature-card:hover {
    transform: translateY(-2px);
    border-color: var(--color-ink);
    box-shadow: var(--shadow-2);
}
.feature-card__title { margin: 0; }
.feature-card__text { margin: 0; font-size: 0.95rem; line-height: 1.55; color: var(--color-ink-soft); }
</style>
