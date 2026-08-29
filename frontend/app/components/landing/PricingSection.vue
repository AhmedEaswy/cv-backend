<script setup lang="ts">
/**
 * <LandingPricing /> — single free plan, featured.
 */
const { t } = useI18n();

const tier = computed(() => ({
    key: 'free',
    name: t('landing.section_pricing_free_name'),
    price: t('landing.section_pricing_free_price'),
    badge: t('landing.section_pricing_free_badge'),
    features: [
        t('landing.section_pricing_feature_1'),
        t('landing.section_pricing_feature_2'),
        t('landing.section_pricing_feature_3'),
        t('landing.section_pricing_feature_4'),
        t('landing.section_pricing_feature_5'),
    ],
}));
</script>

<template>
    <section id="pricing" class="section !pb-0">
        <div class="container-narrow">
            <div class="pricing-head">
                <h2 class="display-2">{{ t('landing.section_pricing_title') }}</h2>
                <p class="lede">{{ t('landing.section_pricing_subtitle') }}</p>
            </div>

            <div class="pricing-grid">
                <article class="price-card price-card--featured">
                    <div class="price-card__badge">
                        <span class="tag tag--soft">{{ tier.badge }}</span>
                    </div>
                    <span class="price-card__name">{{ tier.name }}</span>
                    <div class="price-card__price">{{ tier.price }}</div>
                    <div class="price-card__divider" />
                    <ul class="price-card__features">
                        <li v-for="(f, i) in tier.features" :key="i" class="price-card__feature">
                            <Icon name="check" :size="16" />
                            <span>{{ f }}</span>
                        </li>
                    </ul>
                    <Button
                        to="/auth/register"
                        variant="secondary"
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
    grid-template-columns: minmax(0, 22rem);
    justify-content: center;
    gap: 1.25rem;
}

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
