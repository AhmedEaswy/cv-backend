<script setup lang="ts">
/**
 * <LandingPlatforms /> — "Notely for Android / iOS / Windows / Web" tiles.
 * Mirrors the 2x2 platform grid in the reference.
 */
const { t } = useI18n();
</script>

<template>
    <section class="section">
        <div class="container-narrow">
            <div class="platforms">
                <div class="platform-card platform-card--android">
                    <div class="platform-card__art">
                        <div class="phone-frame">
                            <div class="phone-frame__bar" />
                            <div class="phone-frame__bar phone-frame__bar--sm" />
                            <div class="phone-frame__bar phone-frame__bar--xs" />
                            <div class="phone-frame__cta">Book a Call</div>
                        </div>
                    </div>
                    <h3 class="platform-card__title">{{ t('landing.section_platforms_android') }}</h3>
                    <p class="platform-card__text">{{ t('landing.section_platforms_android_text') }}</p>
                </div>

                <div class="platform-card platform-card--ios">
                    <div class="platform-card__list">
                        <div class="platform-row">
                            <span class="platform-row__icon">📝</span>
                            <div>
                                <div class="platform-row__title">{{ t('landing.hero_pill_note_taken') }}</div>
                                <div class="platform-row__bar" />
                            </div>
                        </div>
                        <div class="platform-row">
                            <span class="platform-row__icon">📁</span>
                            <div>
                                <div class="platform-row__title">{{ t('landing.hero_pill_folders') }}</div>
                                <div class="platform-row__bar" />
                            </div>
                        </div>
                        <div class="platform-row">
                            <span class="platform-row__icon">👥</span>
                            <div>
                                <div class="platform-row__title">{{ t('landing.hero_pill_collaborated') }}</div>
                                <div class="platform-row__bar" />
                            </div>
                        </div>
                    </div>
                    <h3 class="platform-card__title">{{ t('landing.section_platforms_ios') }}</h3>
                    <p class="platform-card__text">{{ t('landing.section_platforms_ios_text') }}</p>
                </div>

                <div class="platform-card platform-card--desktop">
                    <div class="platform-card__art platform-card__art--desktop">
                        <svg viewBox="0 0 240 120" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <g fill="currentColor">
                                <circle cx="60"  cy="60" r="22" opacity="0.9" />
                                <circle cx="100" cy="40" r="18" opacity="0.6" />
                                <circle cx="100" cy="80" r="18" opacity="0.6" />
                                <circle cx="140" cy="60" r="22" opacity="0.3" />
                                <circle cx="180" cy="60" r="14" opacity="0.4" />
                            </g>
                        </svg>
                    </div>
                    <h3 class="platform-card__title">{{ t('landing.section_platforms_desktop') }}</h3>
                    <p class="platform-card__text">{{ t('landing.section_platforms_desktop_text') }}</p>
                </div>

                <div class="platform-card platform-card--web">
                    <div class="platform-card__art platform-card__art--web">
                        <div class="web-ring">
                            <span class="web-pill web-pill--l">{{ t('landing.hero_pill_responsive') }}</span>
                            <span class="web-pill web-pill--r">{{ t('landing.hero_pill_fast') }}</span>
                            <span class="web-pill web-pill--b">
                                <Icon name="shield" :size="14" />
                            </span>
                            <span class="web-ring__center" />
                        </div>
                    </div>
                    <h3 class="platform-card__title">{{ t('landing.section_platforms_web') }}</h3>
                    <p class="platform-card__text">{{ t('landing.section_platforms_web_text') }}</p>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.platforms {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.25rem;
}
@media (min-width: 768px) { .platforms { grid-template-columns: 1fr 1fr; } }

.platform-card {
    background: var(--color-white);
    border: 1px solid var(--color-line);
    border-radius: var(--radius-xl);
    padding: 1.75rem;
    min-height: 280px;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    transition: border-color 0.2s ease, transform 0.2s ease;
}
.platform-card:hover { border-color: var(--color-ink); transform: translateY(-2px); }

.platform-card__art {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--color-ink);
    min-height: 120px;
}
.platform-card__title {
    font-family: var(--font-display);
    font-size: 1.25rem;
    font-weight: 400;
    letter-spacing: -0.015em;
    color: var(--color-ink);
    margin: 0;
}
.platform-card__text {
    margin: 0;
    color: var(--color-ink-soft);
    font-size: 0.875rem;
    line-height: 1.5;
}

.phone-frame {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
    padding: 1rem;
    background: var(--color-paper-2);
    border-radius: var(--radius-md);
    width: 200px;
}
.phone-frame__bar { height: 6px; background: var(--color-ink); border-radius: 3px; opacity: 0.85; width: 80%; }
.phone-frame__bar--sm { width: 50%; opacity: 0.5; }
.phone-frame__bar--xs { width: 35%; opacity: 0.35; height: 4px; }
.phone-frame__cta {
    margin-top: 0.5rem;
    background: var(--color-ink);
    color: var(--color-paper);
    font-size: 0.7rem;
    font-weight: 500;
    padding: 0.4rem 0.75rem;
    border-radius: var(--radius-pill);
    text-align: center;
    align-self: flex-start;
}

.platform-card__list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    padding: 0.5rem 0;
    flex: 1;
    justify-content: center;
}
.platform-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.65rem 0.85rem;
    background: var(--color-paper-2);
    border-radius: var(--radius-md);
}
.platform-row__icon { font-size: 1.1rem; }
.platform-row__title { font-size: 0.8rem; font-weight: 500; color: var(--color-ink); margin-bottom: 0.25rem; }
.platform-row__bar { height: 4px; background: var(--color-line-2); border-radius: 2px; width: 80%; }

.platform-card__art--desktop { color: var(--color-ink); }
.platform-card__art--desktop svg { width: 240px; height: 120px; }

.platform-card__art--web { padding-block: 0.5rem; }
.web-ring {
    position: relative;
    width: 200px;
    height: 130px;
}
.web-ring::before, .web-ring::after {
    content: "";
    position: absolute;
    inset: 0;
    border: 1px dashed var(--color-line-2);
    border-radius: 50%;
    margin: 12px;
}
.web-ring::after {
    margin: 0;
    border-style: solid;
    border-color: var(--color-line);
}
.web-ring__center {
    position: absolute;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    width: 36px; height: 36px;
    background: var(--color-ink);
    border-radius: 50%;
    box-shadow: 0 0 0 6px var(--color-white);
}
.web-pill {
    position: absolute;
    background: var(--color-ink);
    color: var(--color-paper);
    font-size: 0.7rem;
    padding: 0.3rem 0.6rem;
    border-radius: var(--radius-pill);
    font-weight: 500;
}
.web-pill--l { top: 30%; left: -8px; }
.web-pill--r { top: 26%; right: -8px; }
.web-pill--b { bottom: 0; left: 50%; transform: translateX(-50%); background: var(--color-paper-2); color: var(--color-ink); border: 1px solid var(--color-line); }
</style>
