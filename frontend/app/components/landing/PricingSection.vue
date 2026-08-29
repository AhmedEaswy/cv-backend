<script setup lang="ts">
/**
 * <LandingPricing /> — three pricing cards, middle one featured.
 * Mirrors the reference's $0.00 / $69.00 (Pro, Most Popular) / $149.00 (Vision).
 */
const { t } = useI18n();
const config = useRuntimeConfig();
const laravel = (config.public.laravelUrl as string).replace(/\/+$/, '');

const tiers = computed(() => ([
    {
        key: 'free',
        name: t('landing.section_pricing_free_name'),
        price: t('landing.section_pricing_free_price'),
        priceSmall: '',
        features: [
            t('landing.section_pricing_feature_1'),
            t('landing.section_pricing_feature_2'),
            t('landing.section_pricing_feature_3'),
        ],
        featured: false,
    },
    {
        key: 'pro',
        name: t('landing.section_pricing_pro_name'),
        price: t('landing.section_pricing_pro_price'),
        priceSmall: '',
        badge: t('landing.section_pricing_pro_badge'),
        features: [
            t('landing.section_pricing_pro_feature_1'),
            t('landing.section_pricing_pro_feature_2'),
            t('landing.section_pricing_pro_feature_3'),
            t('landing.section_pricing_pro_feature_4'),
        ],
        featured: true,
    },
    {
        key: 'vision',
        name: t('landing.section_pricing_vision_name'),
        price: t('landing.section_pricing_vision_price'),
        priceSmall: '',
        badge: t('landing.section_pricing_vision_badge'),
        features: [
            t('landing.section_pricing_vision_feature_1'),
            t('landing.section_pricing_vision_feature_2'),
            t('landing.section_pricing_vision_feature_3'),
            t('landing.section_pricing_vision_feature_4'),
        ],
        featured: false,
    },
]));
</script>

<template>
    <section id="pricing" class="section">
        <div class="container-narrow">
            <div class="pricing-head">
                <h2 class="display-2">{{ t('landing.section_pricing_title') }}</h2>
                <p class="lede">{{ t('landing.section_pricing_subtitle') }}</p>
            </div>

            <div class="pricing-grid">
                <article
                    v-for="tier in tiers"
                    :key="tier.key"
                    :class="['price-card', tier.featured && 'price-card--featured']"
                >
                    <div v-if="tier.badge" class="price-card__badge">
                        <span class="tag" :class="tier.featured ? 'tag--soft' : 'tag--default'">{{ tier.badge }}</span>
                    </div>
                    <span class="price-card__name">{{ tier.name }}</span>
                    <div class="price-card__price">
                        {{ tier.price }}
                        <small v-if="tier.priceSmall">{{ tier.priceSmall }}</small>
                    </div>
                    <div class="price-card__divider" />
                    <ul class="price-card__features">
                        <li v-for="(f, i) in tier.features" :key="i" class="price-card__feature">
                            <Icon name="check" :size="16" />
                            <span>{{ f }}</span>
                        </li>
                    </ul>
                    <Button
                        :href="laravel + '/register'"
                        :variant="tier.featured ? 'secondary' : 'primary'"
                        block
                    >
                        {{ t('landing.section_pricing_cta_upgrade') }}
                    </Button>
                </article>
            </div>
        </div>
    </section>
</template>

<style scoped>
.pricing-head { text-align: center; margin-bottom: 3rem; max-width: 40rem; margin-inline: auto; }
.pricing-head .display-2 { margin: 0 0 1rem; }
.pricing-head .lede { margin: 0 auto; }

.pricing-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.25rem;
    align-items: stretch;
}
@media (min-width: 768px)  { .pricing-grid { grid-template-columns: repeat(3, 1fr); align-items: center; } }

.price-card { position: relative; }
.price-card__badge {
    position: absolute;
    top: 1rem;
    inset-inline-end: 1rem;
}
.price-card__features {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
    flex: 1;
}
</style>
