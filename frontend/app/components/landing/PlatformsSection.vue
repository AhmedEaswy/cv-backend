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

<style scoped>
/* =========================================================================
   Section shell
   ========================================================================= */
.platforms__head {
    max-width: 42rem;
    margin: 0 auto 3.25rem;
    text-align: center;
}
.platforms__title { margin: 0.85rem 0 1rem; }
.platforms__sub { margin: 0 auto; }

.platforms {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.25rem;
}
@media (min-width: 768px) { .platforms { grid-template-columns: 1fr 1fr; } }

.platform-card {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    padding: 1.5rem;
    background: var(--color-white);
    border: 1px solid var(--color-line);
    border-radius: var(--radius-xl);
    transition: border-color 0.25s ease, transform 0.25s ease, box-shadow 0.25s ease;
}
.platform-card:hover {
    border-color: var(--color-ink);
    transform: translateY(-3px);
    box-shadow: var(--shadow-2);
}

.platform-card__art {
    display: grid;
    place-items: center;
    height: 212px;
    padding: 1.25rem;
    overflow: hidden;
    color: var(--color-ink);
    border: 1px solid var(--color-line);
    border-radius: var(--radius-lg);
    background:
        radial-gradient(120% 90% at 50% -20%, rgba(255, 255, 255, 0.9), transparent 60%),
        linear-gradient(180deg, var(--color-paper) 0%, var(--color-paper-2) 100%);
}

.platform-card__body {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
    padding-inline: 0.25rem;
    padding-bottom: 0.25rem;
}
.platform-card__badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    align-self: flex-start;
    padding: 0.32rem 0.65rem;
    border: 1px solid var(--color-line);
    border-radius: var(--radius-pill);
    background: var(--color-paper-2);
    color: var(--color-ink-soft);
    font-size: 0.7rem;
    font-weight: 500;
    letter-spacing: 0.02em;
    line-height: 1;
}
.platform-card__title {
    margin: 0;
    font-family: var(--font-display);
    font-size: clamp(1.4rem, 2.4vw, 1.75rem);
    font-weight: 400;
    letter-spacing: -0.02em;
    color: var(--color-ink);
}
.platform-card__text {
    margin: 0;
    font-size: 0.9rem;
    line-height: 1.6;
    color: var(--color-ink-soft);
}

/* Every artwork shares the same box so the four cards stay optically aligned. */
.cv-art,
.cover-art,
.profile-art,
.ats-art {
    position: relative;
    width: 100%;
    max-width: 282px;
}

/* =========================================================================
   Art 1 — CV builder
   ========================================================================= */
.cv-art { height: 150px; }

.cv-art__sheet,
.cv-art__ghost {
    position: absolute;
    inset: 0;
    border: 1px solid var(--color-line);
    border-radius: var(--radius-md);
    background: var(--color-white);
}
.cv-art__ghost {
    transform-origin: 50% 100%;
    animation: cv-stack 7s ease-in-out infinite;
}
.cv-art__ghost--far {
    background: var(--color-paper-2);
    animation-delay: -1.1s;
}
.cv-art__ghost--near {
    background: var(--color-paper);
    animation-delay: -0.55s;
}

.cv-art__sheet {
    display: flex;
    flex-direction: column;
    gap: 0.55rem;
    padding: 0.8rem 0.9rem;
    box-shadow: 0 10px 24px -18px rgba(0, 0, 0, 0.5);
}

.cv-art__head {
    display: flex;
    align-items: center;
    gap: 0.55rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid var(--color-line);
}
.cv-art__avatar {
    flex: none;
    width: 26px;
    height: 26px;
    border-radius: 50%;
    background: var(--color-ink);
}
.cv-art__head-lines {
    display: flex;
    flex: 1;
    flex-direction: column;
    gap: 0.3rem;
}

.cv-art__block {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
}

.cv-art__line,
.cv-art__label {
    display: block;
    width: var(--w);
    height: 5px;
    border-radius: 3px;
    background: var(--color-line-2);
    animation: art-write 7s var(--d, 0s) ease-in-out infinite both;
}
.cv-art__line--name { height: 7px; background: var(--color-ink); }
.cv-art__label { background: var(--color-ink-soft); }

.cv-art__chip {
    position: absolute;
    inset-inline-end: -0.4rem;
    bottom: -0.55rem;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.32rem 0.6rem;
    border-radius: var(--radius-pill);
    background: var(--color-ink);
    color: var(--color-paper);
    font-size: 0.65rem;
    font-weight: 500;
    line-height: 1;
    box-shadow: 0 8px 18px -10px rgba(0, 0, 0, 0.7);
    animation: cv-chip 7s ease-in-out infinite both;
}

@keyframes cv-stack {
    0%, 8%    { transform: translateY(0) scale(1); opacity: 0; }
    30%, 78%  { transform: translateY(9px) scale(0.955); opacity: 1; }
    92%, 100% { transform: translateY(0) scale(1); opacity: 0; }
}
@keyframes cv-chip {
    0%, 62%   { transform: translateY(6px) scale(0.9); opacity: 0; }
    72%, 92%  { transform: translateY(0) scale(1); opacity: 1; }
    100%      { transform: translateY(6px) scale(0.9); opacity: 0; }
}

/* =========================================================================
   Art 2 — Cover letter
   ========================================================================= */
.cover-art {
    display: flex;
    flex-direction: column;
    gap: 0.7rem;
}

.cover-art__sheet {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
    padding: 0.8rem 0.9rem 0.9rem;
    border: 1px solid var(--color-line);
    border-radius: var(--radius-md);
    background: var(--color-white);
    box-shadow: 0 10px 24px -18px rgba(0, 0, 0, 0.5);
}

.cover-art__to {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    margin-bottom: 0.3rem;
}
.cover-art__to-label {
    font-size: 0.58rem;
    font-weight: 500;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--color-ink-soft);
}
.cover-art__to-bar {
    flex: 1;
    height: 6px;
    border-radius: 3px;
    background: var(--color-ink);
}

.cover-art__line {
    display: block;
    width: var(--w);
    height: 5px;
    border-radius: 3px;
    background: var(--color-line-2);
    animation: art-write 3s var(--d, 0s) ease-in-out infinite both;
}

.cover-art__sign {
    width: 66px;
    height: 19px;
    margin-top: 0.2rem;
    color: var(--color-ink);
    stroke-dasharray: 220;
    animation: cover-sign 3s ease-in-out infinite both;
}

.cover-art__tones {
    position: relative;
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    padding: 3px;
    border: 1px solid var(--color-line);
    border-radius: var(--radius-pill);
    background: var(--color-white);
}
.cover-art__tone-thumb {
    position: absolute;
    top: 3px;
    bottom: 3px;
    inset-inline-start: 3px;
    width: calc((100% - 6px) / 3);
    border-radius: var(--radius-pill);
    background: var(--color-ink);
    animation: cover-thumb 9s cubic-bezier(0.65, 0, 0.35, 1) infinite;
}
.cover-art__tone {
    position: relative;
    padding: 0.36rem 0.2rem;
    font-size: 0.65rem;
    font-weight: 500;
    line-height: 1;
    text-align: center;
    color: var(--color-ink-soft);
    animation: cover-tone-label 9s ease-in-out infinite;
}
.cover-art__tone:nth-child(3) { animation-delay: -6s; }
.cover-art__tone:nth-child(4) { animation-delay: -3s; }

[dir="rtl"] .cover-art__tone-thumb { animation-name: cover-thumb-rtl; }

@keyframes cover-thumb {
    0%, 27%   { transform: translateX(0); }
    33%, 60%  { transform: translateX(100%); }
    67%, 94%  { transform: translateX(200%); }
    100%      { transform: translateX(0); }
}
@keyframes cover-thumb-rtl {
    0%, 27%   { transform: translateX(0); }
    33%, 60%  { transform: translateX(-100%); }
    67%, 94%  { transform: translateX(-200%); }
    100%      { transform: translateX(0); }
}
/* The label lightens just as the thumb slides under it, never dark-on-dark. */
@keyframes cover-tone-label {
    0%, 32%  { color: var(--color-paper); }
    36%, 95% { color: var(--color-ink-soft); }
    100%     { color: var(--color-paper); }
}
@keyframes cover-sign {
    0%, 42%   { stroke-dashoffset: 220; }
    90%, 95%  { stroke-dashoffset: 0; }
    100%      { stroke-dashoffset: 220; }
}

/* =========================================================================
   Art 3 — Public profile
   ========================================================================= */
.profile-art__window {
    overflow: hidden;
    border: 1px solid var(--color-line);
    border-radius: var(--radius-md);
    background: var(--color-white);
    box-shadow: 0 10px 24px -18px rgba(0, 0, 0, 0.5);
}

.profile-art__bar {
    display: flex;
    align-items: center;
    gap: 0.28rem;
    padding: 0.45rem 0.55rem;
    border-bottom: 1px solid var(--color-line);
    background: var(--color-paper);
}
.profile-art__dot {
    flex: none;
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: var(--color-line-2);
}
.profile-art__url {
    display: flex;
    align-items: center;
    gap: 0.3rem;
    flex: 1;
    min-width: 0;
    margin-inline-start: 0.35rem;
    padding: 0.2rem 0.5rem;
    border-radius: var(--radius-pill);
    background: var(--color-paper-2);
    color: var(--color-ink-soft);
    font-size: 0.62rem;
    line-height: 1.4;
    white-space: nowrap;
}
.profile-art__url-text {
    display: inline-block;
    overflow: hidden;
    max-width: 14ch;
    color: var(--color-ink);
    font-weight: 500;
    vertical-align: bottom;
    animation: profile-type 6s steps(14, end) infinite both;
}
.profile-art__caret {
    flex: none;
    width: 1px;
    height: 0.7rem;
    background: var(--color-ink);
    animation: profile-caret 1.1s linear infinite;
}

.profile-art__page {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
    padding: 0.8rem 0.9rem 0.9rem;
}
.profile-art__avatar {
    width: 28px;
    height: 28px;
    margin-bottom: 0.2rem;
    border-radius: 50%;
    background: var(--color-ink);
}
.profile-art__line {
    display: block;
    width: var(--w);
    height: 6px;
    border-radius: 3px;
    background: var(--color-ink);
}
.profile-art__line--soft { height: 5px; background: var(--color-line-2); }

.profile-art__tiles {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.35rem;
    margin-top: 0.35rem;
}
.profile-art__tiles span {
    height: 26px;
    border: 1px solid var(--color-line);
    border-radius: var(--radius-sm);
    background: var(--color-paper-2);
    animation: profile-tile 3s var(--d, 0s) ease-in-out infinite;
}

.profile-art__live {
    position: absolute;
    top: -0.55rem;
    inset-inline-end: -0.5rem;
    display: inline-flex;
    align-items: center;
    gap: 0.32rem;
    padding: 0.3rem 0.6rem;
    border-radius: var(--radius-pill);
    background: var(--color-ink);
    color: var(--color-paper);
    font-size: 0.62rem;
    font-weight: 500;
    line-height: 1;
    box-shadow: 0 8px 18px -10px rgba(0, 0, 0, 0.7);
}
.profile-art__live-dot {
    width: 5px;
    height: 5px;
    border-radius: 50%;
    background: var(--color-paper);
    animation: profile-ping 2s ease-out infinite;
}

.profile-art__float {
    position: absolute;
    display: inline-grid;
    place-items: center;
    width: 24px;
    height: 24px;
    border: 1px solid var(--color-line);
    border-radius: 50%;
    background: var(--color-white);
    color: var(--color-ink);
    box-shadow: 0 6px 14px -10px rgba(0, 0, 0, 0.6);
    animation: profile-float 5s ease-in-out infinite both;
}
.profile-art__float--1 { bottom: 2.4rem; inset-inline-start: -0.75rem; }
.profile-art__float--2 { bottom: 0.9rem; inset-inline-end: -0.75rem; animation-delay: -2.5s; }

@keyframes profile-type {
    0%, 4%    { max-width: 0; }
    45%, 88%  { max-width: 14ch; }
    97%, 100% { max-width: 0; }
}
@keyframes profile-caret {
    0%, 50%   { opacity: 1; }
    51%, 100% { opacity: 0; }
}
@keyframes profile-ping {
    0%        { box-shadow: 0 0 0 0 rgba(250, 250, 249, 0.6); }
    70%, 100% { box-shadow: 0 0 0 7px rgba(250, 250, 249, 0); }
}
@keyframes profile-tile {
    0%, 100% { transform: translateY(0); border-color: var(--color-line); }
    50%      { transform: translateY(-3px); border-color: var(--color-line-2); }
}
@keyframes profile-float {
    0%, 100% { transform: translateY(8px); opacity: 0; }
    18%, 62% { transform: translateY(-4px); opacity: 1; }
    85%      { transform: translateY(-16px); opacity: 0; }
}

/* =========================================================================
   Art 4 — ATS checker
   ========================================================================= */
.ats-art {
    display: flex;
    align-items: center;
    gap: 0.9rem;
}

.ats-art__doc {
    position: relative;
    display: flex;
    flex: 1;
    flex-direction: column;
    justify-content: center;
    gap: 0.65rem;
    height: 140px;
    padding: 0 0.9rem;
    overflow: hidden;
    border: 1px solid var(--color-line);
    border-radius: var(--radius-md);
    background: var(--color-white);
    box-shadow: 0 10px 24px -18px rgba(0, 0, 0, 0.5);
}
.ats-art__row {
    display: block;
    width: var(--w);
    height: 5px;
    border-radius: 3px;
    background: var(--color-line-2);
    animation: ats-match 3s var(--d, 0s) ease-in-out infinite both;
}
.ats-art__scan {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 34px;
    border-bottom: 1px solid rgba(10, 10, 10, 0.4);
    background: linear-gradient(180deg, transparent, rgba(10, 10, 10, 0.05) 60%, rgba(10, 10, 10, 0.14));
    animation: ats-scan 3s cubic-bezier(0.45, 0, 0.55, 1) infinite;
}

.ats-art__score {
    display: flex;
    flex: none;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
}
.ats-art__gauge {
    position: relative;
    width: 78px;
    height: 78px;
}
.ats-art__ring {
    width: 100%;
    height: 100%;
    transform: rotate(-90deg);
}
.ats-art__ring-track,
.ats-art__ring-value {
    fill: none;
    stroke-width: 4;
    stroke-linecap: round;
}
.ats-art__ring-track { stroke: var(--color-line); }
.ats-art__ring-value {
    stroke: var(--color-ink);
    stroke-dasharray: 126;
    animation: ats-fill 6s ease-in-out infinite both;
}
.ats-art__num {
    position: absolute;
    top: 50%;
    left: 50%;
    height: 1.35rem;
    overflow: hidden;
    transform: translate(-50%, -50%);
    font-size: 1.05rem;
    font-weight: 600;
    line-height: 1.35rem;
    color: var(--color-ink);
}
.ats-art__roll {
    display: block;
    animation: ats-roll 6s cubic-bezier(0.65, 0, 0.35, 1) infinite both;
}
.ats-art__roll b {
    display: block;
    height: 1.35rem;
    font-weight: 600;
}

.ats-art__caption {
    font-size: 0.6rem;
    font-weight: 500;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: var(--color-ink-soft);
}

@keyframes ats-scan {
    0%        { transform: translateY(-34px); opacity: 0; }
    10%, 80%  { opacity: 1; }
    100%      { transform: translateY(140px); opacity: 0; }
}
@keyframes ats-match {
    0%, 4%    { background: var(--color-line-2); }
    12%, 92%  { background: var(--color-ink); }
    99%, 100% { background: var(--color-line-2); }
}
@keyframes ats-fill {
    0%, 5%    { stroke-dashoffset: 126; }
    58%, 90%  { stroke-dashoffset: 8; }
    98%, 100% { stroke-dashoffset: 126; }
}
@keyframes ats-roll {
    0%, 10%   { transform: translateY(0); }
    26%, 40%  { transform: translateY(-1.35rem); }
    56%, 90%  { transform: translateY(-2.7rem); }
    98%, 100% { transform: translateY(0); }
}

/* Shared: a bar that draws itself in, holds, then clears for the next pass. */
@keyframes art-write {
    0%, 4%    { width: 0; opacity: 0.2; }
    20%, 86%  { width: var(--w); opacity: 1; }
    96%, 100% { width: 0; opacity: 0.2; }
}

@media (prefers-reduced-motion: reduce) {
    .platform-card__art *,
    .platform-card__art *::before,
    .platform-card__art *::after {
        animation: none !important;
    }
    .cv-art__ghost { opacity: 1; transform: translateY(9px) scale(0.955); }
    .cv-art__chip { opacity: 1; transform: none; }
    .cover-art__sign { stroke-dashoffset: 0; }
    .profile-art__url-text { max-width: 14ch; }
    .profile-art__float { opacity: 1; transform: none; }
    .ats-art__scan { opacity: 0; }
    .ats-art__row { background: var(--color-ink); }
    .ats-art__ring-value { stroke-dashoffset: 8; }
    .ats-art__roll { transform: translateY(-2.7rem); }
}
</style>
