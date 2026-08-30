# Scoped Style Blocks Report

**Total files:** 38

## Long inline Tailwind class strings (>=60 chars, >=5 utilities)

- `components/landing/CoverFeature.vue` (88 chars, 10 utilities, scoped=false)
  `absolute -top-4 -end-4 w-16 h-16 rounded-full flex items-center justify-center shadow-lg`

- `components/landing/PublicProfileFeature.vue` (87 chars, 10 utilities, scoped=false)
  `absolute -top-4 -end-4 w-16 h-16 rounded-2xl flex items-center justify-center shadow-lg`

- `components/landing/CoverFeature.vue` (81 chars, 9 utilities, scoped=false)
  `inline-flex items-center justify-center w-5 h-5 rounded-full mt-0.5 flex-shrink-0`

- `components/landing/PublicProfileFeature.vue` (81 chars, 9 utilities, scoped=false)
  `inline-flex items-center justify-center w-5 h-5 rounded-full mt-0.5 flex-shrink-0`

- `components/landing/CoverFeature.vue` (77 chars, 8 utilities, scoped=false)
  `font-display text-3xl sm:text-4xl font-bold text-[#130e21] mb-4 leading-tight`

- `components/landing/DownloadCTA.vue` (77 chars, 8 utilities, scoped=false)
  `font-display text-3xl sm:text-5xl font-bold text-[#130e21] mb-5 leading-tight`

- `components/landing/FeaturesBento.vue` (77 chars, 8 utilities, scoped=false)
  `font-display text-3xl sm:text-4xl font-bold text-[#130e21] mb-3 leading-tight`

- `components/landing/PublicProfileFeature.vue` (77 chars, 8 utilities, scoped=false)
  `font-display text-3xl sm:text-4xl font-bold text-[#130e21] mb-4 leading-tight`

- `components/landing/TemplatesCarousel.vue` (77 chars, 8 utilities, scoped=false)
  `font-display text-3xl sm:text-4xl font-bold text-[#130e21] mb-3 leading-tight`

- `components/landing/CoverFeature.vue` (72 chars, 9 utilities, scoped=false)
  `text-[10px] font-semibold uppercase tracking-wider text-[#8a8499] mb-1.5`

- `components/landing/CoverFeature.vue` (72 chars, 9 utilities, scoped=false)
  `text-[10px] font-semibold uppercase tracking-wider text-[#8a8499] mb-1.5`

- `components/landing/CoverFeature.vue` (71 chars, 8 utilities, scoped=false)
  `max-w-6xl mx-auto px-5 sm:px-8 grid lg:grid-cols-12 gap-12 items-center`

- `components/landing/PublicProfileFeature.vue` (71 chars, 8 utilities, scoped=false)
  `max-w-6xl mx-auto px-5 sm:px-8 grid lg:grid-cols-12 gap-12 items-center`

- `components/landing/TemplatesCarousel.vue` (66 chars, 9 utilities, scoped=false)
  `w-[72%] h-[86%] rounded-sm bg-white shadow-md flex overflow-hidden`

- `components/landing/TemplatesCarousel.vue` (65 chars, 11 utilities, scoped=false)
  `w-[72%] h-[86%] rounded-sm bg-white shadow-md p-3.5 flex flex-col`

- `components/landing/TemplatesCarousel.vue` (65 chars, 11 utilities, scoped=false)
  `w-[72%] h-[86%] rounded-sm bg-white shadow-md p-3.5 flex flex-col`

## Full scoped CSS by file

### components/landing/AiChatDemo.vue

```css
.ai-chat {
    display: flex;
    flex-direction: column;
    width: 100%;
    max-width: 28rem;
    min-height: 28rem;
    border-radius: 1.5rem;
    border: 1px solid var(--color-line);
    background: var(--color-white);
    padding: 1.15rem 1.1rem 1rem;
    box-shadow: var(--shadow-2);
}
.ai-chat__head {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.9rem;
}
.ai-chat__dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--color-success);
}
.ai-chat__title {
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--color-ink-soft);
}
.ai-chat__chips {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.45rem;
    margin-bottom: 0.85rem;
}
.ai-chat__chip {
    height: 2.35rem;
    border-radius: 999px;
    border: 1px solid var(--color-line);
    background: var(--color-paper);
    color: var(--color-ink);
    font-size: 0.8rem;
    cursor: pointer;
}
.ai-chat__chip.is-on,
.ai-chat__chip:hover {
    background: var(--color-ink);
    color: var(--color-paper);
    border-color: var(--color-ink);
}
.ai-chat__log {
    flex: 1;
    min-height: 11rem;
    max-height: 16rem;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 0.65rem;
    padding-block: 0.25rem;
    transition: opacity 0.2s ease;
}
.ai-chat__log.is-clearing { opacity: 0; }
.ai-chat__row { display: flex; gap: 0.45rem; align-items: flex-end; }
.ai-chat__row--user { justify-content: flex-end; }
.ai-chat__avatar {
    width: 1.7rem;
    height: 1.7rem;
    border-radius: 50%;
    display: grid;
    place-items: center;
    background: var(--color-paper-2);
    color: var(--color-ink);
    flex-shrink: 0;
}
.ai-chat__bubble {
    margin: 0;
    max-width: 85%;
    padding: 0.55rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.85rem;
    line-height: 1.45;
}
.ai-chat__bubble--bot { background: var(--color-paper-2); color: var(--color-ink); }
.ai-chat__bubble--user { background: var(--color-ink); color: var(--color-paper); }
.ai-chat__pulse {
    width: 5px;
    height: 5px;
    border-radius: 50%;
    background: var(--color-muted);
    display: inline-block;
    margin-inline: 2px;
    animation: ai-pulse 1s ease-in-out infinite;
}
.ai-chat__pulse:nth-child(2) { animation-delay: 0.15s; }
.ai-chat__pulse:nth-child(3) { animation-delay: 0.3s; }
@keyframes ai-pulse {
    0%, 100% { opacity: 0.3; transform: translateY(0); }
    50% { opacity: 1; transform: translateY(-2px); }
}
.ai-chat__composer {
    margin-top: 0.85rem;
    display: flex;
    align-items: center;
    gap: 0.55rem;
    border: 1px solid var(--color-line);
    border-radius: 0.9rem;
    padding: 0.45rem 0.5rem 0.45rem 0.85rem;
    min-height: 3.15rem;
}
.ai-chat__input {
    flex: 1;
    margin: 0;
    font-size: 0.82rem;
    color: var(--color-muted);
    min-width: 0;
}
.ai-chat__typed { color: var(--color-ink); }
.ai-chat__typed.has-caret::after {
    content: '';
    display: inline-block;
    width: 1px;
    height: 0.9em;
    margin-inline-start: 2px;
    background: var(--color-ink);
    animation: ai-caret 1s step-end infinite;
    vertical-align: text-bottom;
}
@keyframes ai-caret { 50% { opacity: 0; } }
.ai-chat__send {
    width: 2.2rem;
    height: 2.2rem;
    border-radius: 50%;
    display: grid;
    place-items: center;
    background: var(--color-ink);
    color: var(--color-paper);
    flex-shrink: 0;
}
.ai-chat__send.is-pulse { transform: scale(0.94); }
@media (prefers-reduced-motion: reduce) {
    .ai-chat__pulse, .ai-chat__typed.has-caret::after { animation: none; }
}
```

### components/landing/AiConnectModal.vue

```css
.ai-modal__lead { margin: 0 0 1rem; color: var(--color-ink-soft); line-height: 1.5; }
.ai-modal__tabs {
    display: flex;
    gap: 0.35rem;
    margin-bottom: 1rem;
    background: var(--color-paper-2);
    padding: 0.25rem;
    border-radius: 999px;
    width: fit-content;
}
.ai-modal__tabs button {
    border: 0;
    background: transparent;
    padding: 0.4rem 0.9rem;
    border-radius: 999px;
    font-size: 0.85rem;
    cursor: pointer;
    color: var(--color-ink-soft);
}
.ai-modal__tabs button[aria-selected='true'] {
    background: var(--color-white);
    color: var(--color-ink);
    box-shadow: var(--shadow-1);
}
.ai-modal__steps {
    margin: 0 0 1rem;
    padding-inline-start: 1.2rem;
    color: var(--color-ink);
    font-size: 0.92rem;
    line-height: 1.7;
    list-style: decimal;
}
.ai-modal__grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.6rem;
}
@media (max-width: 640px) {
    .ai-modal__grid { grid-template-columns: repeat(2, 1fr); }
}
.ai-modal__platform {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.35rem;
    padding: 0.7rem 0.4rem;
    border-radius: 0.75rem;
    border: 1px solid var(--color-line);
    background: linear-gradient(180deg, #fff, #f3f3f3);
    cursor: pointer;
    font-size: 0.75rem;
    color: var(--color-ink);
}
.ai-modal__platform img { object-fit: contain; }
.ai-modal__platform:hover { border-color: var(--color-ink); }
.ai-modal__other {
    margin-top: 0.85rem;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    border: 1px dashed var(--color-line-2);
    background: transparent;
    border-radius: 999px;
    padding: 0.45rem 0.9rem;
    cursor: pointer;
}
.ai-modal__hint { color: var(--color-muted); font-size: 0.8rem; margin: 0.5rem 0 0; }
.ai-modal__mcp p { color: var(--color-ink-soft); line-height: 1.5; }
.ai-modal__label { display: block; font-size: 0.75rem; font-weight: 600; margin: 0.9rem 0 0.35rem; }
.ai-modal__code-row {
    display: flex;
    gap: 0.5rem;
    align-items: center;
    flex-wrap: wrap;
}
.ai-modal__code-row code {
    font-size: 0.8rem;
    background: var(--color-paper-2);
    padding: 0.35rem 0.55rem;
    border-radius: 0.4rem;
}
.ai-modal__pre {
    background: var(--color-paper-2);
    padding: 0.75rem;
    border-radius: 0.6rem;
    font-size: 0.75rem;
    overflow-x: auto;
}
```

### components/landing/AiConnectSection.vue

```css
.ai-connect { background: var(--color-paper); }
.ai-connect__grid {
    display: grid;
    gap: 2.5rem;
    align-items: center;
}
@media (min-width: 900px) {
    .ai-connect__grid { grid-template-columns: 1fr 1fr; }
}
.ai-connect h2 { margin: 0.4rem 0 0.85rem; max-width: 18ch; }
.ai-connect .lede { max-width: 36rem; }
.ai-connect__bullets {
    margin: 0 0 1.4rem;
    padding: 0;
    list-style: none;
    display: grid;
    gap: 0.45rem;
}
.ai-connect__bullets li {
    padding-inline-start: 1.4rem;
    position: relative;
    color: var(--color-ink-soft);
    font-size: 0.95rem;
}
.ai-connect__bullets li::before {
    content: '';
    position: absolute;
    inset-inline-start: 0;
    top: 0.45rem;
    width: 0.55rem;
    height: 0.55rem;
    border-radius: 50%;
    background: var(--color-ink);
}
```

### components/landing/DownloadSection.vue

```css
.closing-cta__panel {
    position: relative;
    overflow: hidden;
    margin-inline: auto;
    padding: clamp(3rem, 8vw, 4.5rem) 1.5rem;
    border-radius: 1.35rem;
    background:
        radial-gradient(ellipse 80% 60% at 50% 0%, rgba(255, 255, 255, 0.07), transparent 65%),
        linear-gradient(180deg, #121212 0%, #0a0a0a 100%);
    border: 1px solid rgba(255, 255, 255, 0.06);
    text-align: center;
    isolation: isolate;
}

@media (min-width: 768px) {
    .closing-cta__panel {
        border-radius: 1.5rem;
        padding: clamp(3.5rem, 8vw, 5rem) 2.5rem;
    }
}

.closing-cta__glow {
    pointer-events: none;
    position: absolute;
    inset: -30% -10% auto;
    height: 70%;
    background: radial-gradient(ellipse 50% 40% at 50% 0%, rgba(255, 255, 255, 0.08), transparent 70%);
    z-index: 0;
}

.closing-cta__title {
    position: relative;
    z-index: 1;
    margin: 0 auto 1rem;
    max-width: 16ch;
    font-family: var(--font-display);
    font-size: clamp(2rem, 5vw, 3rem);
    line-height: 1.08;
    letter-spacing: -0.03em;
    font-weight: 400;
    color: #ffffff;
    text-wrap: balance;
}

.closing-cta__subtitle {
    position: relative;
    z-index: 1;
    margin: 0 auto 2rem;
    max-width: 34rem;
    font-size: 1rem;
    line-height: 1.6;
    color: rgba(255, 255, 255, 0.55);
}

.closing-cta__badges {
    position: relative;
    z-index: 1;
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    justify-content: center;
}

.closing-cta__badge {
    display: inline-flex;
    align-items: center;
    gap: 0.65rem;
    padding: 0.65rem 1.15rem 0.65rem 0.9rem;
    border-radius: var(--radius-pill);
    background: #f4f4f2;
    color: #0a0a0a;
    text-decoration: none;
    border: 1px solid rgba(255, 255, 255, 0.12);
    box-shadow: 0 12px 32px -12px rgba(0, 0, 0, 0.45);
    transition: background 0.15s ease, transform 0.15s ease, box-shadow 0.15s ease;
}

.closing-cta__badge:hover {
    background: #ffffff;
    transform: translateY(-1px);
    box-shadow: 0 16px 36px -12px rgba(0, 0, 0, 0.5);
}

.closing-cta__badge img {
    flex-shrink: 0;
    display: block;
}

.closing-cta__badge-text {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    line-height: 1.1;
    text-align: start;
}

.closing-cta__badge-caption {
    font-size: 0.65rem;
    font-weight: 500;
    letter-spacing: 0.01em;
    color: rgba(10, 10, 10, 0.65);
}

.closing-cta__badge-label {
    font-size: 0.95rem;
    font-weight: 600;
    letter-spacing: -0.01em;
}

.closing-cta__note {
    position: relative;
    z-index: 1;
    margin: 1.25rem 0 0;
    font-size: 0.85rem;
    color: rgba(255, 255, 255, 0.38);
}
```

### components/landing/FeaturesSection.vue

```css
.features-head { text-align: center; margin-bottom: 3.5rem; max-width: 40rem; margin-inline: auto; }
.features-head .display-2 { margin: 0 0 1rem; }
.features-head .lede { margin: 0 auto; }

.features-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.25rem;
}
@media (min-width: 768px) { .features-grid { grid-template-columns: repeat(2, 1fr); } }
@media (min-width: 1024px) { .features-grid { grid-template-columns: repeat(3, 1fr); } }

.feature-card {
    background: var(--color-white);
    border: 1px solid var(--color-line);
    border-radius: var(--radius-xl);
    padding: 1.75rem;
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
    transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
    min-height: 240px;
}
.feature-card:hover {
    transform: translateY(-2px);
    border-color: var(--color-ink);
    box-shadow: var(--shadow-2);
}
.feature-card__title { margin: 0; }
.feature-card__text { margin: 0; font-size: 0.95rem; line-height: 1.55; color: var(--color-ink-soft); }
```

### components/landing/HeroSection.vue

```css
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
    width: calc(var(--radius) * 3.4);
    height: calc(var(--radius) * 3.4);
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

.hero-orbit__btn--register {
    background: #f4f4f2 !important;
    color: #0a0a0a !important;
    border-color: transparent !important;
    box-shadow: 0 10px 30px -12px rgba(0, 0, 0, 0.55);
    padding-inline: 1.25rem 1.15rem !important;
    font-weight: 600;
    gap: 0.45rem;
}

.hero-orbit__btn--register:hover {
    background: #ffffff !important;
    transform: translateY(-1px);
    box-shadow: 0 14px 36px -10px rgba(0, 0, 0, 0.6);
}

.hero-orbit__btn--ai {
    background: rgba(255, 255, 255, 0.08) !important;
    color: #ffffff !important;
    border: 1px solid rgba(255, 255, 255, 0.18) !important;
    padding-inline: 1.05rem 1.3rem !important;
}

.hero-orbit__btn--ai:hover {
    background: rgba(255, 255, 255, 0.14) !important;
    border-color: rgba(255, 255, 255, 0.28) !important;
    transform: translateY(-1px);
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
    .hero-orbit__btn--ai,
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
```

### components/landing/MarqueeStrip.vue

```css
.marquee-item {
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 0.4rem;
}
.marquee-item__label {
    font-family: var(--font-display);
    font-size: 1.15rem;
    line-height: 1;
    color: var(--color-ink);
    letter-spacing: -0.015em;
}
.marquee-item__meta {
    font-size: 0.75rem;
    color: var(--color-muted);
    letter-spacing: 0.04em;
    text-transform: uppercase;
}
```

### components/landing/MockupSection.vue

```css
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
```

### components/landing/PlatformsSection.vue

```css
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
    display: none;
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
```

### components/landing/PricingSection.vue

```css
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
```

### components/landing/SiteFooter.vue

```css
.site-footer {
    width: 100%;
    background: var(--color-white);
    border-top: 1px solid var(--color-line);
}

.site-footer__inner {
    max-width: 76rem;
    margin-inline: auto;
    padding: clamp(2.5rem, 5vw, 3.5rem) 1.25rem 2rem;
}

@media (min-width: 768px) {
    .site-footer__inner {
        padding-inline: 2rem;
    }
}

@media (min-width: 1024px) {
    .site-footer__inner {
        padding-inline: 2.5rem;
    }
}

.site-footer__grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 2rem 2.5rem;
    margin-bottom: 2.5rem;
}

@media (min-width: 768px) {
    .site-footer__grid {
        grid-template-columns: 1.4fr 1fr 1fr 1fr;
        gap: 2rem;
    }
}

.site-footer__brand-mark {
    display: inline-flex;
    align-items: center;
    gap: 0.55rem;
    text-decoration: none;
    color: var(--color-ink);
    font-weight: 600;
    margin-bottom: 0.85rem;
}

.site-footer__brand-icon {
    display: inline-grid;
    place-items: center;
    width: 100px;
    height: 100px;
    overflow: hidden;
}

.site-footer__brand-icon img {
    display: block;
    width: 100px;
    height: 100px;
    object-fit: contain;
}

.site-footer__brand-text {
    margin: 0;
    max-width: 22rem;
    font-size: 0.92rem;
    line-height: 1.55;
    color: var(--color-ink-soft);
}

.site-footer__col h4 {
    margin: 0 0 1rem;
    font-size: 0.95rem;
    font-weight: 600;
    letter-spacing: -0.01em;
    color: var(--color-ink);
}

.site-footer__col ul {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 0.65rem;
}

.site-footer__col a {
    font-size: 0.9rem;
    color: var(--color-ink-soft);
    text-decoration: none;
    transition: color 0.15s ease;
}

.site-footer__col a:hover {
    color: var(--color-ink);
}

.site-footer__rule {
    height: 1px;
    background: var(--color-line);
    margin-bottom: 1.25rem;
}

.site-footer__bottom {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}

.site-footer__copy {
    margin: 0;
    font-size: 0.82rem;
    color: var(--color-muted);
}

.site-footer__legal {
    display: inline-flex;
    flex-wrap: wrap;
    gap: 1.25rem;
}

.site-footer__legal a {
    font-size: 0.82rem;
    color: var(--color-muted);
    text-decoration: underline;
    text-underline-offset: 3px;
    transition: color 0.15s ease;
}

.site-footer__legal a:hover {
    color: var(--color-ink);
}
```

### components/landing/SiteHeader.vue

```css
.site-header {
    position: fixed;
    top: 0.75rem;
    left: 0;
    right: 0;
    z-index: 40;
    padding: 0.85rem 0.75rem 0;
    pointer-events: none;
}

@media (min-width: 768px) {
    .site-header {
        top: 1rem;
        padding: 1rem 1rem 0;
    }
}

.site-header__bar {
    pointer-events: auto;
    max-width: 72rem;
    margin-inline: auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    min-height: 3.25rem;
    padding: 0.45rem 0.55rem 0.45rem 0.9rem;
    border-radius: 9999px;
    background: rgba(12, 12, 12, 0.55);
    border: 1px solid rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    box-shadow: 0 10px 40px -20px rgba(0, 0, 0, 0.6);
    transition: background 0.2s ease, border-color 0.2s ease;
}

.site-header--scrolled .site-header__bar {
    background: rgba(8, 8, 8, 0.82);
    border-color: rgba(255, 255, 255, 0.12);
}

.site-header__brand {
    display: flex;
    align-items: center;
    flex-shrink: 0;
}

.site-header__logo {
    height: 1.55rem;
    width: auto;
}

@media (min-width: 640px) {
    .site-header__logo {
        height: 1.75rem;
    }
}

.site-header__nav {
    display: none;
    align-items: center;
    gap: 1.6rem;
    font-size: 0.875rem;
    color: rgba(255, 255, 255, 0.72);
}

.site-header__nav a {
    text-decoration: none;
    color: inherit;
    transition: color 0.15s ease;
}

.site-header__nav a:hover {
    color: #ffffff;
}

@media (min-width: 768px) {
    .site-header__nav {
        display: flex;
    }
}

.site-header__actions {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    flex-shrink: 0;
}

.site-header__ghost {
    display: none;
    align-items: center;
    padding: 0.45rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.85rem;
    font-weight: 500;
    color: rgba(255, 255, 255, 0.78);
    text-decoration: none;
    transition: color 0.15s ease, background 0.15s ease;
}

.site-header__ghost:hover {
    color: #fff;
    background: rgba(255, 255, 255, 0.06);
}

@media (min-width: 640px) {
    .site-header__ghost {
        display: inline-flex;
    }
}

.site-header__cta {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.55rem 1rem;
    border-radius: 9999px;
    font-size: 0.85rem;
    font-weight: 500;
    color: #0a0a0a;
    background: #f4f4f2;
    text-decoration: none;
    white-space: nowrap;
    transition: background 0.15s ease, transform 0.15s ease;
}

.site-header__cta:hover {
    background: #ffffff;
    transform: translateY(-1px);
}

/* Lang switcher contrast on dark bar */
.site-header__actions :deep(.lang-trigger) {
    color: rgba(255, 255, 255, 0.8);
}
.site-header__actions :deep(.lang-trigger:hover),
.site-header__actions :deep(.lang-trigger[aria-expanded='true']) {
    color: #0a0a0a;
    background: #f4f4f2;
    border-color: transparent;
}
```

### components/landing/TemplatesSection.vue

```css
.tpl-head { text-align: center; margin-bottom: 3rem; }
.tpl-head__title { margin: 0.75rem 0 1rem; }
.tpl-head__sub { margin: 0 auto; }

.tpl-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.25rem;
}
@media (min-width: 640px)  { .tpl-grid { grid-template-columns: repeat(2, 1fr); } }
@media (min-width: 1024px) { .tpl-grid { grid-template-columns: repeat(3, 1fr); } }

.tpl-tile {
    display: block;
    text-decoration: none;
    color: inherit;
    transition: transform 0.2s ease;
}
.tpl-tile:hover { transform: translateY(-3px); }
.tpl-tile:hover .tpl-preview { border-color: var(--color-ink); box-shadow: var(--shadow-3); }

.tpl-preview {
    aspect-ratio: 3 / 4;
    background: var(--color-paper-2);
    border: 1px solid var(--color-line);
    border-radius: var(--radius-md);
    padding: 1.5rem;
    margin-bottom: 1rem;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
    overflow: hidden;
}
.tpl-preview--editorial { background: linear-gradient(180deg, #fafaf9 0%, #f4f4f2 100%); }
.tpl-preview--modern    { background: var(--color-white); border-color: var(--color-ink); }
.tpl-preview--slate     { background: #1a1a1a; }
.tpl-preview--slate .tpl-mock__line { background: rgba(255,255,255,0.18) !important; }
.tpl-preview--slate .tpl-mock__section { background: rgba(255,255,255,0.12) !important; }
.tpl-preview--slate .tpl-mock__avatar { background: rgba(255,255,255,0.4) !important; }
.tpl-preview--slate .tpl-mock__title > span { background: rgba(255,255,255,0.5) !important; }
.tpl-preview--forest    { background: linear-gradient(135deg, #f0ede5 0%, #e3ddc9 100%); }
.tpl-preview--midnight  { background: linear-gradient(180deg, #0a0a0a 0%, #1a1a1a 100%); }
.tpl-preview--midnight .tpl-mock__line { background: rgba(255,255,255,0.18) !important; }
.tpl-preview--midnight .tpl-mock__section { background: rgba(255,255,255,0.12) !important; }
.tpl-preview--midnight .tpl-mock__avatar { background: rgba(255,255,255,0.4) !important; }
.tpl-preview--midnight .tpl-mock__title > span { background: rgba(255,255,255,0.5) !important; }
.tpl-preview--portrait  { background: var(--color-white); }

.tpl-mock {
    background: rgba(255, 255, 255, 0.6);
    border-radius: 6px;
    padding: 0.85rem;
    height: 100%;
    box-shadow: 0 8px 20px -10px rgba(0,0,0,0.18);
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
}
.tpl-preview--slate .tpl-mock,
.tpl-preview--midnight .tpl-mock { background: rgba(255, 255, 255, 0.05); }
.tpl-mock__header { display: flex; align-items: center; gap: 0.6rem; margin-bottom: 0.4rem; }
.tpl-mock__avatar { width: 24px; height: 24px; border-radius: 50%; background: var(--color-ink); flex-shrink: 0; }
.tpl-mock__title { flex: 1; display: flex; flex-direction: column; gap: 3px; }
.tpl-mock__title > span { display: block; height: 5px; background: var(--color-ink); border-radius: 2px; opacity: 0.85; width: 60%; }
.tpl-mock__title > span.short { width: 40%; opacity: 0.5; height: 4px; }
.tpl-mock__line { height: 4px; background: var(--color-line-2); border-radius: 2px; }
.tpl-mock__section { height: 1px; background: var(--color-ink); opacity: 0.2; margin-block: 0.4rem; width: 30%; }

.tpl-meta h3 {
    font-family: var(--font-display);
    font-size: 1.15rem;
    font-weight: 400;
    letter-spacing: -0.015em;
    color: var(--color-ink);
    margin: 0 0 0.25rem;
}
.tpl-meta p {
    margin: 0;
    font-size: 0.85rem;
    color: var(--color-ink-soft);
}
```

### components/layout/SiteHeader.vue

```css
.landing-header {
    position: sticky;
    top: 0;
    z-index: 40;
    background: rgba(250, 250, 249, 0.75);
    backdrop-filter: saturate(180%) blur(14px);
    -webkit-backdrop-filter: saturate(180%) blur(14px);
    border-bottom: 1px solid var(--color-line);
}
.landing-header__inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1.5rem;
    padding-block: 0.9rem;
}
.landing-brand {
    display: inline-flex;
    align-items: center;
    gap: 0.55rem;
    text-decoration: none;
    color: var(--color-ink);
    font-weight: 600;
    font-size: 0.95rem;
    letter-spacing: -0.01em;
}
.landing-brand__mark {
    width: 30px;
    height: 30px;
    border-radius: 8px;
    background: var(--color-ink);
    color: var(--color-paper);
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.landing-brand__text { line-height: 1; }
.landing-nav {
    display: none;
    align-items: center;
    gap: 0.25rem;
}
@media (min-width: 900px) {
    .landing-nav { display: inline-flex; }
}
.landing-nav a {
    padding: 0.45rem 0.9rem;
    border-radius: var(--radius-pill);
    color: var(--color-ink-soft);
    text-decoration: none;
    font-size: 0.875rem;
    font-weight: 500;
    transition: background 0.15s ease, color 0.15s ease;
}
.landing-nav a:hover { color: var(--color-ink); background: var(--color-paper-2); }
.landing-header__actions {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
}
```

### components/legal/LegalDoc.vue

```css
.legal-doc__head {
    margin-bottom: 2.25rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid var(--color-line);
}

.legal-doc__title {
    margin: 0 0 0.65rem;
    font-family: var(--font-display);
    font-size: clamp(2rem, 4vw, 2.75rem);
    font-weight: 400;
    letter-spacing: -0.03em;
    line-height: 1.15;
}

.legal-doc__updated {
    margin: 0;
    font-size: 0.9rem;
    color: var(--color-muted);
}

.legal-doc__body {
    display: flex;
    flex-direction: column;
    gap: 1.75rem;
}

.legal-doc__body :deep(h2) {
    margin: 0.5rem 0 0;
    font-size: 1.15rem;
    font-weight: 600;
    letter-spacing: -0.01em;
}

.legal-doc__body :deep(p),
.legal-doc__body :deep(li) {
    margin: 0;
    font-size: 0.98rem;
    line-height: 1.65;
    color: var(--color-ink-soft);
}

.legal-doc__body :deep(ul) {
    margin: 0;
    padding-inline-start: 1.25rem;
    display: flex;
    flex-direction: column;
    gap: 0.45rem;
}

.legal-doc__body :deep(a) {
    color: var(--color-ink);
    text-underline-offset: 3px;
}
```

### components/portal/AtsModal.vue

```css
.ats { display: flex; flex-direction: column; gap: 1.25rem; }
.modal__sub { margin: 0.4rem 0 0; color: var(--color-ink-soft); font-size: 0.875rem; }

.ats__tabs { display: inline-flex; gap: 0.25rem; padding: 0.25rem; background: var(--color-paper-2); border-radius: var(--radius-pill); align-self: flex-start; }
.ats__tab {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.45rem 0.85rem;
    border-radius: var(--radius-pill);
    border: 0;
    background: transparent;
    color: var(--color-ink-soft);
    font-size: 0.85rem;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.15s ease, color 0.15s ease;
}
.ats__tab--active { background: var(--color-ink); color: var(--color-paper); }

.upload-zone {
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 1.5rem;
    background: var(--color-paper-2);
    border: 1px dashed var(--color-line-2);
    border-radius: var(--radius-md);
    color: var(--color-ink-soft);
    cursor: pointer;
    transition: border-color 0.15s ease, background 0.15s ease;
}
.upload-zone:hover { border-color: var(--color-ink); background: var(--color-white); }
.upload-zone input { display: none; }
.upload-zone__file { display: inline-flex; align-items: center; gap: 0.4rem; color: var(--color-ink); font-weight: 500; }
.upload-zone__hint { font-size: 0.875rem; }

.ats__result { display: flex; flex-direction: column; gap: 1.5rem; }
.ats__score-wrap { display: flex; justify-content: center; padding-block: 0.5rem; }

.ats__section-title { font-family: var(--font-display); font-size: 1.05rem; font-weight: 400; margin: 0 0 0.5rem; letter-spacing: -0.015em; }

.ats__bars { display: flex; flex-direction: column; gap: 0.65rem; }

.ats__keywords { display: flex; flex-direction: column; gap: 0.6rem; }
.ats__kw { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
.ats__kw-title { font-size: 0.78rem; color: var(--color-ink-soft); margin-bottom: 0.4rem; }
.ats__kw-chips { display: flex; flex-wrap: wrap; gap: 0.3rem; }

.ats__checks { border-top: 1px solid var(--color-line); padding-top: 1rem; }
.ats__checks summary { cursor: pointer; color: var(--color-ink-soft); font-size: 0.85rem; margin-bottom: 0.5rem; }
.ats__check-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.4rem; max-height: 220px; overflow-y: auto; }
.ats__check { display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 0.7rem; border-radius: var(--radius-sm); background: var(--color-paper-2); font-size: 0.825rem; }
.ats__check--pass { color: var(--color-success); }
.ats__check--fail { color: var(--color-ink-soft); }
.ats__check > span:nth-child(2) { flex: 1; }
.ats__check :deep(.tag) { margin-inline-start: auto; }

.ats__placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    padding: 2rem 0;
    color: var(--color-ink-soft);
    font-size: 0.9rem;
}

.ats__loading { display: flex; align-items: center; gap: 0.5rem; color: var(--color-ink-soft); font-size: 0.875rem; }
.ats__spinner {
    width: 14px;
    height: 14px;
    border: 2px solid var(--color-ink-soft);
    border-top-color: transparent;
    border-radius: 50%;
    animation: ats-spin 0.7s linear infinite;
    display: inline-block;
}
@keyframes ats-spin { to { transform: rotate(360deg); } }
```

### components/ui/Button.vue

```css
.btn-spinner {
    width: 14px;
    height: 14px;
    border: 2px solid currentColor;
    border-top-color: transparent;
    border-radius: 50%;
    animation: btn-spin 0.7s linear infinite;
    display: inline-block;
}
@keyframes btn-spin { to { transform: rotate(360deg); } }
```

### components/ui/Toaster.vue

```css
.toaster {
    position: fixed;
    bottom: 1.25rem;
    inset-inline-end: 1.25rem;
    z-index: 70;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    pointer-events: none;
    max-width: calc(100% - 2.5rem);
}
.toast {
    display: inline-flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0.75rem 1rem;
    background: var(--color-white);
    color: var(--color-ink);
    border: 1px solid var(--color-line);
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-3);
    font-size: 0.875rem;
    pointer-events: auto;
    cursor: pointer;
    max-width: 24rem;
}
.toast--success { border-inline-start: 3px solid var(--color-success); }
.toast--error   { border-inline-start: 3px solid var(--color-danger); }
.toast--info    { border-inline-start: 3px solid var(--color-ink); }

.toast-enter-active, .toast-leave-active { transition: opacity 0.2s ease, transform 0.2s ease; }
.toast-enter-from { opacity: 0; transform: translateY(8px); }
.toast-leave-to   { opacity: 0; transform: translateY(8px); }
```

### layouts/auth.vue

```css
.auth-aside__brand {
    display: inline-flex;
    align-items: center;
    gap: 0.55rem;
    color: var(--color-paper);
    text-decoration: none;
    font-weight: 600;
    font-size: 0.95rem;
}
.auth-aside__mark {
    width: 30px;
    height: 30px;
    border-radius: 8px;
    background: var(--color-paper);
    color: var(--color-ink);
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.auth-aside__brand-text { line-height: 1; }
.auth-aside__foot {
    font-size: 0.78rem;
    color: rgba(250, 250, 249, 0.45);
}
```

### layouts/legal.vue

```css
.legal-shell {
    min-height: 100vh;
    background: var(--color-paper);
    color: var(--color-ink);
}

.legal-header {
    position: sticky;
    top: 0;
    z-index: 20;
    background: rgba(250, 250, 249, 0.9);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border-bottom: 1px solid var(--color-line);
}

.legal-header__inner {
    max-width: 48rem;
    margin-inline: auto;
    padding: 0.85rem 1.25rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}

.legal-header__brand {
    display: inline-flex;
    align-items: center;
}

.legal-header__logo {
    height: 1.6rem;
    width: auto;
}

.legal-header__nav {
    display: flex;
    align-items: center;
    gap: 1rem;
    font-size: 0.85rem;
}

.legal-header__nav a {
    color: var(--color-ink-soft);
    text-decoration: none;
}

.legal-header__nav a:hover,
.legal-header__nav a.router-link-active {
    color: var(--color-ink);
}

.legal-header__home {
    font-weight: 500;
}

.legal-main {
    max-width: 48rem;
    margin-inline: auto;
    padding: 2.5rem 1.25rem 4rem;
}
```

### layouts/portal.vue

```css
.portal-topbar__right {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}
.portal-avatar { position: relative; }
.portal-avatar__btn {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: var(--color-ink);
    color: var(--color-paper);
    border: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.78rem;
    cursor: pointer;
    transition: transform 0.15s ease;
}
.portal-avatar__btn:hover { transform: scale(1.04); }
.portal-avatar__initials { line-height: 1; }
.portal-avatar__menu {
    position: absolute;
    top: calc(100% + 8px);
    inset-inline-end: 0;
    min-width: 14rem;
    background: var(--color-white);
    border: 1px solid var(--color-line);
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-3);
    padding: 0.35rem;
    z-index: 50;
    opacity: 0;
    transform: translateY(-4px);
    pointer-events: none;
    transition: opacity 0.12s ease, transform 0.12s ease;
}
.portal-avatar__menu.open { opacity: 1; transform: translateY(0); pointer-events: auto; }
.portal-avatar__head { padding: 0.65rem 0.75rem 0.5rem; border-bottom: 1px solid var(--color-line); margin-bottom: 0.35rem; }
.portal-avatar__name { font-weight: 600; color: var(--color-ink); font-size: 0.875rem; }
.portal-avatar__email { color: var(--color-muted); font-size: 0.78rem; margin-top: 0.1rem; }
.portal-avatar__item {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0.5rem 0.7rem;
    border-radius: var(--radius-sm);
    color: var(--color-ink);
    text-decoration: none;
    font-size: 0.875rem;
    background: transparent;
    border: 0;
    width: 100%;
    cursor: pointer;
    text-align: start;
    transition: background 0.1s ease;
    font-family: inherit;
}
.portal-avatar__item:hover { background: var(--color-paper-2); }
.portal-avatar__item--danger { color: var(--color-danger); }
.portal-avatar__item--danger:hover { background: var(--color-danger-soft); }
```

### pages/auth/forgot-password.vue

```css
.field { margin-bottom: 1rem; }
.auth-foot { margin-top: 1.5rem; font-size: 0.85rem; display: flex; gap: 0.5rem; align-items: center; }
.auth-foot :deep(a) { display: inline-flex; align-items: center; gap: 0.3rem; }
```

### pages/auth/login.vue

```css
.field { margin-bottom: 1rem; }
.pwd-wrap { position: relative; }
.pwd-wrap .input { padding-right: 2.75rem; }
.pwd-toggle {
    position: absolute;
    inset-inline-end: 0.6rem;
    top: 50%;
    transform: translateY(-50%);
    background: transparent;
    border: 0;
    color: var(--color-ink-soft);
    cursor: pointer;
    padding: 0.35rem;
    border-radius: var(--radius-sm);
}
.pwd-toggle:hover { background: var(--color-paper-2); color: var(--color-ink); }
.auth-foot {
    margin-top: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
    font-size: 0.85rem;
}
.auth-foot__sep { color: var(--color-muted); }
```

### pages/auth/register.vue

```css
.field { margin-bottom: 1rem; }
.auth-foot {
    margin-top: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.85rem;
    color: var(--color-ink-soft);
}
```

### pages/auth/reset-password.vue

```css
.field { margin-bottom: 1rem; }
.auth-foot { margin-top: 1.5rem; font-size: 0.85rem; display: flex; gap: 0.5rem; align-items: center; }
.auth-foot :deep(a) { display: inline-flex; align-items: center; gap: 0.3rem; }
```

### pages/auth/verify-email.vue

```css
.verify { text-align: center; }
.verify__icon {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background: var(--color-paper-2);
    color: var(--color-ink);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1.5rem;
}
.verify__cta { display: flex; flex-direction: column; gap: 0.5rem; margin-top: 1.5rem; }
```

### pages/index.vue

```css
.landing-closing {
    background: var(--color-paper-2);
    padding: 2rem 1rem 0;
}

@media (min-width: 768px) {
    .landing-closing {
        padding: 0rem 1.25rem 0;
    }
}

.landing-closing__shell {
    max-width: 72rem;
    margin-inline: auto;
}

@media (min-width: 768px) {
    .landing-closing__shell {
        padding: 3rem 1rem;
        border-radius: 2rem;
    }
}
```

### pages/portal/cover-letters/[id]/edit.vue

```css
.back-link { display: inline-flex; align-items: center; gap: 0.3rem; color: var(--color-ink-soft); text-decoration: none; font-size: 0.825rem; margin-bottom: 0.5rem; }
.back-link:hover { color: var(--color-ink); }
.form-card { padding: 1.75rem; max-width: 56rem; }
.field { margin-bottom: 1rem; }
.field-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
@media (max-width: 640px) { .field-grid { grid-template-columns: 1fr; } }
.form-actions { display: flex; justify-content: flex-end; gap: 0.5rem; }
```

### pages/portal/cover-letters/create.vue

```css
.form-card { padding: 1.75rem; max-width: 32rem; }
.field { margin-bottom: 1rem; }
.form-actions { display: flex; justify-content: flex-end; gap: 0.5rem; }
```

### pages/portal/cover-letters/index.vue

```css
.list { display: flex; flex-direction: column; gap: 0.65rem; }
.list-card__link { color: inherit; text-decoration: none; border-bottom: 1px solid transparent; transition: border-color 0.15s ease; }
.list-card__link:hover { border-color: var(--color-ink); }
```

### pages/portal/cvs/[id]/edit.vue

```css
.back-link {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    color: var(--color-ink-soft);
    text-decoration: none;
    font-size: 0.825rem;
    margin-bottom: 0.5rem;
    transition: color 0.15s ease;
}
.back-link:hover { color: var(--color-ink); }
.form-card { padding: 1.75rem; max-width: 48rem; }
.field { margin-bottom: 1rem; }
.field-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
@media (max-width: 640px) { .field-grid { grid-template-columns: 1fr; } }
.form-actions { display: flex; justify-content: flex-end; gap: 0.5rem; }
```

### pages/portal/cvs/create.vue

```css
.form-card { padding: 1.75rem; max-width: 32rem; }
.field { margin-bottom: 1.25rem; }
.form-actions { display: flex; justify-content: flex-end; gap: 0.5rem; }
```

### pages/portal/cvs/index.vue

```css
.list { display: flex; flex-direction: column; gap: 0.65rem; }
.list-card__link {
    color: inherit;
    text-decoration: none;
    border-bottom: 1px solid transparent;
    transition: border-color 0.15s ease;
}
.list-card__link:hover { border-color: var(--color-ink); }
```

### pages/portal/inbox.vue

```css
.filter-pill {
    display: inline-flex;
    gap: 0.25rem;
    padding: 0.25rem;
    background: var(--color-paper-2);
    border: 1px solid var(--color-line);
    border-radius: var(--radius-pill);
}
.filter-pill__btn {
    padding: 0.4rem 0.85rem;
    border-radius: var(--radius-pill);
    border: 0;
    background: transparent;
    color: var(--color-ink-soft);
    font-size: 0.825rem;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.15s ease, color 0.15s ease;
}
.filter-pill__btn.active { background: var(--color-ink); color: var(--color-paper); }

.inbox-layout {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1rem;
}
@media (min-width: 1024px) { .inbox-layout { grid-template-columns: 22rem 1fr; } }

.inbox-list { display: flex; flex-direction: column; gap: 0.4rem; max-height: 60vh; overflow-y: auto; }
.inbox-item {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
    padding: 0.85rem 1rem;
    background: var(--color-white);
    border: 1px solid var(--color-line);
    border-radius: var(--radius-md);
    text-align: start;
    cursor: pointer;
    transition: border-color 0.15s ease, background 0.15s ease;
    font-family: inherit;
    color: inherit;
}
.inbox-item:hover { border-color: var(--color-ink); }
.inbox-item--unread { border-inline-start: 3px solid var(--color-ink); }
.inbox-item--active { border-color: var(--color-ink); background: var(--color-paper-2); }
.inbox-item__head { display: flex; align-items: center; justify-content: space-between; }
.inbox-item__name { font-weight: 600; font-size: 0.9rem; color: var(--color-ink); }
.inbox-item__date { font-size: 0.75rem; color: var(--color-muted); }
.inbox-item__sub { font-size: 0.825rem; color: var(--color-ink-soft); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

.inbox-detail { padding: 1.5rem 1.75rem; min-height: 60vh; }
.inbox-detail--empty { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.75rem; color: var(--color-ink-soft); }
.inbox-detail__head { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1rem; }
.inbox-detail__name { font-family: var(--font-display); font-size: 1.5rem; font-weight: 400; margin: 0 0 0.2rem; letter-spacing: -0.02em; }
.inbox-detail__email { color: var(--color-ink-soft); text-decoration: none; font-size: 0.875rem; }
.inbox-detail__email:hover { text-decoration: underline; }
.inbox-detail__subject { font-weight: 600; color: var(--color-ink); margin: 0 0 0.85rem; }
.inbox-detail__msg { color: var(--color-ink); line-height: 1.6; white-space: pre-wrap; }
.inbox-detail__actions { margin-top: 1.5rem; display: flex; gap: 0.5rem; }
```

### pages/portal/index.vue

```css
.stats-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1rem;
    margin-bottom: 1.5rem;
}
@media (min-width: 640px)  { .stats-grid { grid-template-columns: 1fr 1fr; } }
@media (min-width: 1024px) { .stats-grid { grid-template-columns: repeat(4, 1fr); } }
.stats-grid .stat__value { display: inline-flex; align-items: center; min-height: 2.5rem; }

.dash-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1rem;
    margin-bottom: 1.5rem;
}
@media (min-width: 1024px) { .dash-grid { grid-template-columns: 1fr 1fr; } }

.dash-card { padding: 1.5rem; }
.dash-card__head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; }
.dash-card__title { font-family: var(--font-display); font-size: 1.35rem; font-weight: 400; margin: 0; letter-spacing: -0.02em; }

.dash-list-item {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    padding: 0.85rem;
    background: var(--color-paper-2);
    border-radius: var(--radius-md);
    border: 1px solid var(--color-line);
}
.dash-tip {
    display: flex;
    align-items: flex-start;
    gap: 0.85rem;
    padding: 1.25rem 1.5rem;
    background: var(--color-ink);
    color: var(--color-paper);
    border: 1px solid var(--color-ink);
    border-radius: var(--radius-lg);
}
.dash-tip__icon {
    width: 36px;
    height: 36px;
    border-radius: var(--radius-md);
    background: rgba(255,255,255,0.12);
    color: var(--color-paper);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.dash-tip__title { font-size: 0.9rem; font-weight: 600; margin: 0 0 0.2rem; }
.dash-tip__text { font-size: 0.85rem; margin: 0; color: rgba(250,250,249,0.78); line-height: 1.5; }
```

### pages/portal/public-profile.vue

```css
.form-card { padding: 1.75rem; max-width: 56rem; }
.field { margin-bottom: 1rem; }
.field-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
@media (max-width: 640px) { .field-grid { grid-template-columns: 1fr; } }
.form-actions { display: flex; justify-content: flex-end; gap: 0.5rem; }
```

### pages/portal/settings.vue

```css
.form-card { padding: 1.75rem; max-width: 48rem; }
.form-card__title { font-family: var(--font-display); font-size: 1.5rem; font-weight: 400; margin: 0 0 0.25rem; letter-spacing: -0.02em; }
.form-card__sub { color: var(--color-ink-soft); font-size: 0.9rem; margin: 0 0 1.5rem; }
.field { margin-bottom: 1rem; }
.field-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
@media (max-width: 640px) { .field-grid { grid-template-columns: 1fr; } }
.form-actions { display: flex; justify-content: flex-end; gap: 0.5rem; }
```

### pages/portal/settings/ai-access.vue

```css
.form-card { padding: 1.75rem; max-width: 48rem; }
.form-card__title { font-family: var(--font-display); font-size: 1.5rem; font-weight: 400; margin: 0 0 0.25rem; }
.form-card__sub { color: var(--color-ink-soft); font-size: 0.9rem; margin: 0 0 1.5rem; }
.field { margin-bottom: 1rem; }
.token-form { display: flex; gap: 0.75rem; align-items: flex-end; flex-wrap: wrap; }
.token-form .field { flex: 1; min-width: 12rem; margin-bottom: 0; }
.token-once {
    display: flex;
    gap: 0.6rem;
    align-items: center;
    flex-wrap: wrap;
    margin-bottom: 1rem;
    padding: 0.75rem;
    background: var(--color-paper-2);
    border-radius: 0.75rem;
}
.token-once code { font-size: 0.78rem; word-break: break-all; }
.token-list { list-style: none; padding: 0; margin: 1.5rem 0 0; display: grid; gap: 0.6rem; }
.token-row {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    align-items: center;
    padding: 0.75rem 0;
    border-top: 1px solid var(--color-line);
}
.token-row p { margin: 0.15rem 0 0; color: var(--color-muted); font-size: 0.8rem; }
.token-empty { color: var(--color-muted); }
.tabs a, .tabs span {
    display: inline-flex;
    padding: 0.45rem 0.9rem;
}
```

