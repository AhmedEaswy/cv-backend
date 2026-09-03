<script setup lang="ts">
/**
 * <LandingPlatformsSection /> — the four products, one card each:
 * CV builder, cover letter, public profile, ATS checker.
 *
 * Each card carries a self-contained CSS "art" that loops forever — no JS
 * timers, no scroll observers — and collapses to a static frame under
 * prefers-reduced-motion.
 */
const { t } = useI18n();
</script>

<template>
    <section id="platforms" class="section" aria-labelledby="platforms-title">
        <div class="container-narrow">
            <header class="platforms__head">
                <span class="eyebrow eyebrow--plain">{{ t('landing.platforms_eyebrow') }}</span>
                <h2 id="platforms-title" class="display-2 platforms__title">
                    {{ t('landing.platforms_title') }}
                </h2>
                <p class="lede platforms__sub">{{ t('landing.platforms_subtitle') }}</p>
            </header>

            <div class="platforms">
                <!-- ── CV builder: the sheet writes itself, then exports ── -->
                <article class="platform-card">
                    <div class="platform-card__art" aria-hidden="true">
                        <div class="cv-art">
                            <span class="cv-art__ghost cv-art__ghost--far" />
                            <span class="cv-art__ghost cv-art__ghost--near" />

                            <div class="cv-art__sheet">
                                <div class="cv-art__head">
                                    <span class="cv-art__avatar" />
                                    <span class="cv-art__head-lines">
                                        <i class="cv-art__line cv-art__line--name" style="--w: 74%; --d: 0.1s" />
                                        <i class="cv-art__line" style="--w: 48%; --d: 0.35s" />
                                    </span>
                                </div>

                                <div class="cv-art__block">
                                    <i class="cv-art__label" style="--w: 30%; --d: 0.8s" />
                                    <i class="cv-art__line" style="--w: 94%; --d: 1s" />
                                    <i class="cv-art__line" style="--w: 82%; --d: 1.2s" />
                                </div>

                                <div class="cv-art__block">
                                    <i class="cv-art__label" style="--w: 24%; --d: 1.7s" />
                                    <i class="cv-art__line" style="--w: 88%; --d: 1.9s" />
                                    <i class="cv-art__line" style="--w: 62%; --d: 2.1s" />
                                </div>
                            </div>

                            <span class="cv-art__chip">
                                <Icon name="download" :size="11" />
                                {{ t('landing.platforms_art_pdf') }}
                            </span>
                        </div>
                    </div>

                    <div class="platform-card__body">
                        <span class="platform-card__badge">
                            <Icon name="file-plus" :size="13" />
                            {{ t('landing.platforms_cv_badge') }}
                        </span>
                        <h3 class="platform-card__title">{{ t('landing.platforms_cv_title') }}</h3>
                        <p class="platform-card__text">{{ t('landing.platforms_cv_text') }}</p>
                    </div>
                </article>

                <!-- ── Cover letter: rewrites itself on every tone switch ── -->
                <article class="platform-card">
                    <div class="platform-card__art" aria-hidden="true">
                        <div class="cover-art">
                            <div class="cover-art__sheet">
                                <div class="cover-art__to">
                                    <span class="cover-art__to-label">{{ t('landing.platforms_art_to') }}</span>
                                    <i class="cover-art__to-bar" />
                                </div>

                                <i class="cover-art__line" style="--w: 92%; --d: 0.1s" />
                                <i class="cover-art__line" style="--w: 100%; --d: 0.25s" />
                                <i class="cover-art__line" style="--w: 86%; --d: 0.4s" />
                                <i class="cover-art__line" style="--w: 66%; --d: 0.55s" />

                                <svg class="cover-art__sign" viewBox="0 0 84 24" fill="none" aria-hidden="true">
                                    <path
                                        d="M2 18c6-1 9-14 13-14s2 15 7 15 6-12 10-12 3 10 8 10 7-9 11-9 4 7 9 7 8-4 12-9"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            </div>

                            <div class="cover-art__tones">
                                <span class="cover-art__tone-thumb" />
                                <span class="cover-art__tone">{{ t('landing.platforms_art_tone_formal') }}</span>
                                <span class="cover-art__tone">{{ t('landing.platforms_art_tone_friendly') }}</span>
                                <span class="cover-art__tone">{{ t('landing.platforms_art_tone_confident') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="platform-card__body">
                        <span class="platform-card__badge">
                            <Icon name="mail" :size="13" />
                            {{ t('landing.platforms_cover_badge') }}
                        </span>
                        <h3 class="platform-card__title">{{ t('landing.platforms_cover_title') }}</h3>
                        <p class="platform-card__text">{{ t('landing.platforms_cover_text') }}</p>
                    </div>
                </article>

                <!-- ── Public profile: the slug types itself, live ── -->
                <article class="platform-card">
                    <div class="platform-card__art" aria-hidden="true">
                        <div class="profile-art">
                            <div class="profile-art__window">
                                <div class="profile-art__bar">
                                    <span class="profile-art__dot" />
                                    <span class="profile-art__dot" />
                                    <span class="profile-art__dot" />
                                    <span class="profile-art__url">
                                        <Icon name="lock" :size="10" />
                                        <span class="profile-art__url-text">
                                            {{ t('landing.platforms_art_url') }}
                                        </span>
                                        <i class="profile-art__caret" />
                                    </span>
                                </div>

                                <div class="profile-art__page">
                                    <span class="profile-art__avatar" />
                                    <i class="profile-art__line" style="--w: 56%" />
                                    <i class="profile-art__line profile-art__line--soft" style="--w: 38%" />
                                    <div class="profile-art__tiles">
                                        <span style="--d: 0s" />
                                        <span style="--d: 0.35s" />
                                        <span style="--d: 0.7s" />
                                    </div>
                                </div>
                            </div>

                            <span class="profile-art__live">
                                <i class="profile-art__live-dot" />
                                {{ t('landing.platforms_art_live') }}
                            </span>

                            <span class="profile-art__float profile-art__float--1">
                                <Icon name="eye" :size="12" />
                            </span>
                            <span class="profile-art__float profile-art__float--2">
                                <Icon name="mail" :size="12" />
                            </span>
                        </div>
                    </div>

                    <div class="platform-card__body">
                        <span class="platform-card__badge">
                            <Icon name="globe" :size="13" />
                            {{ t('landing.platforms_profile_badge') }}
                        </span>
                        <h3 class="platform-card__title">{{ t('landing.platforms_profile_title') }}</h3>
                        <p class="platform-card__text">{{ t('landing.platforms_profile_text') }}</p>
                    </div>
                </article>

                <!-- ── ATS checker: the scan sweeps, the score climbs ── -->
                <article class="platform-card">
                    <div class="platform-card__art" aria-hidden="true">
                        <div class="ats-art">
                            <div class="ats-art__doc">
                                <i class="ats-art__row" style="--w: 90%; --d: 1.2s" />
                                <i class="ats-art__row" style="--w: 72%; --d: 1.45s" />
                                <i class="ats-art__row" style="--w: 84%; --d: 1.75s" />
                                <i class="ats-art__row" style="--w: 58%; --d: 2s" />
                                <i class="ats-art__row" style="--w: 78%; --d: 2.25s" />
                                <span class="ats-art__scan" />
                            </div>

                            <div class="ats-art__score">
                                <div class="ats-art__gauge">
                                    <svg class="ats-art__ring" viewBox="0 0 48 48" aria-hidden="true">
                                        <circle class="ats-art__ring-track" cx="24" cy="24" r="20" />
                                        <circle class="ats-art__ring-value" cx="24" cy="24" r="20" />
                                    </svg>
                                    <span class="ats-art__num">
                                        <span class="ats-art__roll">
                                            <b>41</b>
                                            <b>76</b>
                                            <b>94</b>
                                        </span>
                                    </span>
                                </div>
                                <span class="ats-art__caption">{{ t('landing.platforms_art_score') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="platform-card__body">
                        <span class="platform-card__badge">
                            <Icon name="target" :size="13" />
                            {{ t('landing.platforms_ats_badge') }}
                        </span>
                        <h3 class="platform-card__title">{{ t('landing.platforms_ats_title') }}</h3>
                        <p class="platform-card__text">{{ t('landing.platforms_ats_text') }}</p>
                    </div>
                </article>
            </div>
        </div>
    </section>
</template>

