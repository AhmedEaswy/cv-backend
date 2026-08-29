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
const playStore = (config.public.playStoreUrl as string) || '#download';
const { user } = await useAuth();

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

const titleLines = computed(() => {
    const line1 = t('landing.hero_title_line1');
    const line2 = t('landing.hero_title_line2');
    const hasSplitKeys =
        line1 !== 'landing.hero_title_line1' && line2 !== 'landing.hero_title_line2';
    if (hasSplitKeys) return [line1, line2];
    return String(t('landing.hero_title')).split(/<br\s*\/?>/i);
});

const trackDownload = () => {
    if (!import.meta.client) return;
    const api = useApi();
    api('/analytics/click', { method: 'POST', body: { label: 'hero_download_app', page: 'landing' } }).catch(
        () => undefined,
    );
};
</script>

<template>
    <section class="hero-orbit" aria-labelledby="hero-orbit-title">
        <div class="hero-orbit__frame">
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
                            :href="playStore"
                            variant="secondary"
                            size="md"
                            class="hero-orbit__btn hero-orbit__btn--download"
                            target="_blank"
                            rel="noopener"
                            @click="trackDownload"
                        >
                            <Icon name="download" :size="16" />
                            {{ t('landing.hero_cta_download') }}
                        </Button>
                        <Button
                            :to="registerTo"
                            variant="ghost"
                            size="md"
                            class="hero-orbit__btn hero-orbit__btn--register"
                        >
                            <span class="hero-orbit__btn-avatar" aria-hidden="true">
                                <img
                                    :src="`${laravel}/images/logo-icon.png`"
                                    alt=""
                                    width="22"
                                    height="22"
                                />
                            </span>
                            {{ registerLabel }}
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.hero-orbit {
    position: relative;
    z-index: 0;
    padding: 0.75rem 0.75rem 0;
    background: var(--color-paper);
}

.hero-orbit__frame {
    position: relative;
    overflow: hidden;
    min-height: min(92vh, 860px);
    border-radius: 1.75rem;
    background: #050505;
    color: #f5f5f5;
    isolation: isolate;
}

@media (min-width: 768px) {
    .hero-orbit {
        padding: 1rem 1rem 0;
    }
    .hero-orbit__frame {
        border-radius: 2rem;
        min-height: min(94vh, 920px);
    }
}

.hero-orbit__beams {
    pointer-events: none;
    position: absolute;
    inset: -20% -10% auto;
    height: 70%;
    background:
        conic-gradient(
            from 200deg at 50% 0%,
            transparent 0deg,
            rgba(255, 255, 255, 0.05) 18deg,
            transparent 36deg,
            transparent 48deg,
            rgba(255, 255, 255, 0.035) 62deg,
            transparent 82deg,
            transparent 110deg,
            rgba(255, 255, 255, 0.045) 130deg,
            transparent 150deg,
            transparent 360deg
        );
    filter: blur(2px);
    opacity: 0.95;
    z-index: 0;
}

.hero-orbit__glow {
    pointer-events: none;
    position: absolute;
    inset: 0;
    background:
        radial-gradient(ellipse 55% 40% at 50% 0%, rgba(255, 255, 255, 0.1), transparent 70%),
        radial-gradient(ellipse 70% 55% at 50% 55%, transparent 35%, rgba(0, 0, 0, 0.35) 100%);
    z-index: 0;
}

.hero-orbit__stage {
    position: relative;
    z-index: 1;
    display: grid;
    place-items: center;
    min-height: inherit;
    padding: 6.5rem 1.25rem 4rem;
    isolation: isolate;
}

/*
 * Cards mounted with rotate(angle) + translateY(-radius)
 * so they tilt along the arc like the reference (not upright).
 * Mask fades the bottom of the ring for depth-of-field.
 */
.hero-orbit__ring-wrap {
    --radius: min(40vw, 355px);
    position: absolute;
    top: 48%;
    left: 50%;
    z-index: 0;
    width: calc(var(--radius) * 2.4);
    height: calc(var(--radius) * 2.4);
    transform: translate(-50%, -50%);
    pointer-events: none;
    -webkit-mask-image: linear-gradient(
        180deg,
        #000 0%,
        #000 58%,
        rgba(0, 0, 0, 0.55) 78%,
        transparent 96%
    );
    mask-image: linear-gradient(
        180deg,
        #000 0%,
        #000 58%,
        rgba(0, 0, 0, 0.55) 78%,
        transparent 96%
    );
}

.hero-orbit__ring {
    position: relative;
    width: 100%;
    height: 100%;
    animation: hero-orbit-spin 50s linear infinite;
    will-change: transform;
}

.hero-orbit__slot {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    /* Mount on the wheel — tilt follows the circle as it spins */
    transform: rotate(var(--angle)) translateY(calc(var(--radius) * -1));
}

.hero-orbit__card {
    width: clamp(78px, 10.5vw, 118px);
    aspect-ratio: 10 / 13;
    border-radius: 16px;
    overflow: hidden;
    background: linear-gradient(160deg, #1c1c1f 0%, #0c0c0e 100%);
    border: 1px solid rgba(255, 255, 255, 0.14);
    box-shadow:
        0 22px 40px -16px rgba(0, 0, 0, 0.9),
        0 0 0 1px rgba(255, 255, 255, 0.05) inset,
        0 0 32px -6px rgba(140, 160, 255, 0.28);
    /* Center on the slot; no counter-rotate so cards lean with the arc */
    transform: translate(-50%, -50%) scale(var(--scale));
}

.hero-orbit__card img {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: top center;
    user-select: none;
}

@keyframes hero-orbit-spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.hero-orbit__content {
    position: relative;
    z-index: 2;
    max-width: 36rem;
    text-align: center;
    padding: 1.25rem 0.75rem;
    border-radius: 1.5rem;
    background: radial-gradient(ellipse 70% 65% at 50% 50%, rgba(5, 5, 5, 0.88) 0%, rgba(5, 5, 5, 0.45) 55%, transparent 78%);
}

.hero-orbit__title {
    margin: 0 0 1.15rem;
    font-family: var(--font-display);
    font-size: clamp(2.35rem, 6.5vw, 4rem);
    line-height: 1.05;
    letter-spacing: -0.03em;
    font-weight: 400;
    color: #ffffff;
    text-wrap: balance;
}

.hero-orbit__title-line {
    display: block;
}

.hero-orbit__subtitle {
    margin: 0 auto 1.75rem;
    max-width: 28rem;
    font-size: 0.98rem;
    line-height: 1.55;
    color: rgba(255, 255, 255, 0.55);
}

.hero-orbit__cta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.65rem;
    justify-content: center;
}

.hero-orbit__btn--download {
    background: #f4f4f2 !important;
    color: #0a0a0a !important;
    border-color: transparent !important;
    box-shadow: 0 10px 30px -12px rgba(0, 0, 0, 0.55);
    padding-inline: 1.15rem 1.4rem !important;
}

.hero-orbit__btn--download:hover {
    background: #ffffff !important;
    transform: translateY(-1px);
}

.hero-orbit__btn--register {
    background: rgba(255, 255, 255, 0.08) !important;
    color: #ffffff !important;
    border: 1px solid rgba(255, 255, 255, 0.18) !important;
    padding-inline: 1.05rem 1.3rem !important;
}

.hero-orbit__btn--register:hover {
    background: rgba(255, 255, 255, 0.14) !important;
    border-color: rgba(255, 255, 255, 0.28) !important;
    transform: translateY(-1px);
}

.hero-orbit__btn-avatar {
    display: inline-grid;
    place-items: center;
    width: 26px;
    height: 26px;
    border-radius: 50%;
    overflow: hidden;
    background: #111;
    margin-inline-end: 0.15rem;
}

.hero-orbit__btn-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

@media (max-width: 700px) {
    .hero-orbit__frame {
        min-height: min(88vh, 720px);
        border-radius: 1.25rem;
    }
    .hero-orbit__ring-wrap {
        --radius: min(46vw, 220px);
        width: calc(var(--radius) * 2.5);
        height: calc(var(--radius) * 2.5);
    }
    .hero-orbit__card {
        width: clamp(58px, 16vw, 80px);
        border-radius: 12px;
    }
    .hero-orbit__slot:nth-child(3n) {
        display: none;
    }
    .hero-orbit__cta {
        flex-direction: column;
        align-items: stretch;
        padding-inline: 1rem;
    }
    .hero-orbit__btn--download,
    .hero-orbit__btn--register {
        width: 100%;
        justify-content: center;
    }
}

@media (prefers-reduced-motion: reduce) {
    .hero-orbit__ring {
        animation: none;
    }
}
</style>
