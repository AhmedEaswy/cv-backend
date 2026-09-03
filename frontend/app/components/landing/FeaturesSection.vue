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

