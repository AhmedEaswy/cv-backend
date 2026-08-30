<script setup lang="ts">
/**
 * <LandingMockupSection /> — full-width dark band with a two-column grid:
 * flat mockup image on one side, CV product copy on the other.
 */
const { t } = useI18n();
const config = useRuntimeConfig();
const laravel = (config.public.laravelUrl as string).replace(/\/+$/, '');
</script>

<template>
    <section id="mockup" class="mockup-section" aria-labelledby="mockup-section-title">
        <div class="mockup-section__grid">
            <div class="mockup-section__media">
                <img
                    :src="`${laravel}/images/mockup-flat.png`"
                    :alt="t('landing.section_mockup_image_alt')"
                    class="mockup-section__image"
                    width="960"
                    height="960"
                    loading="lazy"
                    decoding="async"
                />
            </div>

            <div class="mockup-section__copy">
                <h2 id="mockup-section-title" class="display-2 mockup-section__title">
                    {{ t('landing.section_mockup_title') }}
                </h2>
                <p class="lede mockup-section__lede">
                    {{ t('landing.section_mockup_subtitle') }}
                </p>
                <p class="mockup-section__text">{{ t('landing.section_mockup_paragraph_1') }}</p>
                <p class="mockup-section__text">{{ t('landing.section_mockup_paragraph_2') }}</p>
            </div>
        </div>
    </section>
</template>

<style scoped>
.mockup-section {
    padding: 6rem 0 0 0;
    overflow: hidden;
    background:
        radial-gradient(ellipse 70% 55% at 18% 50%, rgba(255, 255, 255, 0.06), transparent 70%),
        linear-gradient(180deg, #121212 0%, #0a0a0a 100%);
    border-block: 1px solid rgba(255, 255, 255, 0.06);
}

.mockup-section__grid {
    display: grid;
    grid-template-columns: 1fr;
    align-items: stretch;
    width: 100%;
}

@media (min-width: 1024px) {
    .mockup-section__grid {
        grid-template-columns: 1fr 1fr;
    }
}

.mockup-section__media {
    order: 2;
    display: flex;
    align-items: flex-end;
    justify-content: center;
    padding-inline: 1rem;
    padding-bottom: 0;
}

@media (min-width: 1024px) {
    .mockup-section__media {
        order: 1;
        padding-inline: clamp(1rem, 2.5vw, 2rem);
    }
}

.mockup-section__image {
    display: block;
    width: min(100%, 18rem);
    height: auto;
    object-fit: contain;
    object-position: bottom center;
}

@media (min-width: 768px) {
    .mockup-section__image {
        width: min(100%, 20rem);
    }
}

@media (min-width: 1024px) {
    .mockup-section__image {
        width: min(100%, 22rem);
    }
}

.mockup-section__copy {
    order: 1;
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
    padding: 2.5rem 1rem;
}

@media (min-width: 768px) {
    .mockup-section__copy {
        padding: 3rem 1.5rem;
    }
}

@media (min-width: 1024px) {
    .mockup-section__copy {
        order: 2;
        align-self: center;
        padding: 3.5rem clamp(1rem, 2.5vw, 2rem) 3.5rem clamp(1rem, 3vw, 2.5rem);
        max-width: 34rem;
    }
}

.mockup-section__eyebrow {
    display: inline-flex;
    align-items: center;
    align-self: flex-start;
    padding: 0.35rem 0.7rem;
    border: 1px solid rgba(255, 255, 255, 0.14);
    border-radius: var(--radius-pill);
    background: rgba(255, 255, 255, 0.06);
    color: rgba(255, 255, 255, 0.72);
    font-size: 0.72rem;
    font-weight: 500;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    line-height: 1;
}

.mockup-section__title {
    margin: 0;
    color: #ffffff;
}

.mockup-section__lede {
    margin: 0;
    max-width: none;
    color: rgba(255, 255, 255, 0.68);
}

.mockup-section__text {
    margin: 0;
    font-size: 0.95rem;
    line-height: 1.65;
    color: rgba(255, 255, 255, 0.52);
}
</style>
