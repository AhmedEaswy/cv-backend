<script setup lang="ts">
/**
 * <LandingHeroSection />
 *
 * Dark circular-card hero inspired by the Seestem CTA:
 * mockups are mounted on a spinning ring (tilted along the arc),
 * with depth fade at the edges and dual CTAs.
 */
const { t } = useI18n();
const config = useRuntimeConfig();
const laravel = (config.public.laravelUrl as string).replace(/\/+$/, '');
const { user } = await useAuthUser();

const asideBgSrc = `${laravel}/images/cover-papers.jpg`;

/** Template / profile previews mounted around the ring. */
const orbitImages = [
    `${laravel}/images/templates/professional.png`,
    `${laravel}/images/templates/ats-classic.png`,
    `${laravel}/images/templates/modern-professional.png`,
    `${laravel}/images/public-profile-templates/bold-poster.png`,
    `${laravel}/images/public-profile-templates/dark-terminal.png`,
    `${laravel}/images/public-profile-templates/soft-pastel.png`,
    `${laravel}/images/public-profile-templates/editorial-serif.png`,
    `${laravel}/images/public-profile-templates/minimal-folio.png`,
    `${laravel}/images/public-profile-templates/corporate-split.png`,
    `${laravel}/images/public-profile-templates/gallery-masonry.png`,
    `${laravel}/images/public-profile-templates/timeline-vertical.png`,
    `${laravel}/images/public-profile-templates/cardless-gradient.png`,
    `${laravel}/images/mockups/cv-modern.svg`,
    `${laravel}/images/mockups/cover-letter.svg`,
] as const;

type OrbitCard = {
    src: string;
    angle: number;
    scale: number;
};

/** Even spacing; each card is rotated with the ring so it tilts along the arc. */
const orbitCards = computed<OrbitCard[]>(() => {
    const count = orbitImages.length;
    return orbitImages.map((src, i) => ({
        src,
        angle: (i / count) * 360,
        scale: 0.9 + ((i % 4) * 0.04),
    }));
});

const registerTo = computed(() => (user.value ? '/portal' : '/auth/register'));
const registerLabel = computed(() =>
    user.value ? t('landing.hero_cta_primary_authed') : t('landing.hero_cta_register'),
);
const { show: showAiConnect } = useAiConnectModal();
const track = useClickTracker();

const openAiConnect = () => {
    track('hero_connect_ai', 'landing');
    showAiConnect();
};

const titleLines = computed(() => {
    const line1 = t('landing.hero_title_line1');
    const line2 = t('landing.hero_title_line2');
    const hasSplitKeys =
        line1 !== 'landing.hero_title_line1' && line2 !== 'landing.hero_title_line2';
    if (hasSplitKeys) return [line1, line2];
    return String(t('landing.hero_title')).split(/<br\s*\/?>/i);
});
</script>

<template>
    <section class="hero-orbit" aria-labelledby="hero-orbit-title">
        <div class="hero-orbit__frame">

            <div class="absolute z-0 size-full inset-0">
                <img :src="asideBgSrc" alt="Logo" class="absolute inset w-full z-0 max-h-full max-w-full opacity-10 object-cover" />
                <div class="bg-gradient-to-b from-black to-transparent absolute inset-0 z-10 pointer-events-none h-full w-full"></div>
            </div>

            <div class="hero-orbit__beams" aria-hidden="true" />
            <div class="hero-orbit__glow" aria-hidden="true" />

            <div class="hero-orbit__stage">
                <div class="hero-orbit__ring-wrap" aria-hidden="true">
                    <div class="hero-orbit__ring">
                        <div
                            v-for="(card, i) in orbitCards"
                            :key="i"
                            class="hero-orbit__slot"
                            :style="{
                                '--angle': `${card.angle}deg`,
                                '--scale': card.scale,
                            }"
                        >
                            <div class="hero-orbit__card">
                                <img
                                    :src="card.src"
                                    alt=""
                                    loading="eager"
                                    decoding="async"
                                    draggable="false"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="hero-orbit__content">
                    <h1 id="hero-orbit-title" class="hero-orbit__title">
                        <span
                            v-for="(line, i) in titleLines"
                            :key="i"
                            class="hero-orbit__title-line"
                        >{{ line }}</span>
                    </h1>

                    <p class="hero-orbit__subtitle">
                        {{ t('landing.hero_subtitle') }}
                    </p>

                    <div class="hero-orbit__cta">
                        <Button
                            type="button"
                            variant="ghost"
                            size="md"
                            class="hero-orbit__btn hero-orbit__btn--ai"
                            @click="openAiConnect"
                        >
                            <Icon name="sparkles" :size="16" />
                            {{ t('landing.hero_cta_ai') }}
                        </Button>
                        <Button
                            :to="registerTo"
                            variant="secondary"
                            size="md"
                            class="hero-orbit__btn hero-orbit__btn--register"
                        >
                            {{ registerLabel }}
                            <Icon name="arrow-right" :size="16" class="rtl:rotate-180 ltr:rotate-0" />
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

