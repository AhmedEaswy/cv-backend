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

