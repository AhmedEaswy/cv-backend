# Scribblit UI / UX Guide

Canonical visual identity for **web** (`frontend/` Nuxt) and **mobile** (Flutter / React Native / native). Source of truth for tokens: `frontend/app/assets/css/base.css`. When web and this doc diverge, update both.

**Product:** Scribblit — CV, cover letter, and public profile builder  
**Locales:** English (LTR), Arabic (RTL), Turkish (LTR)  
**Do not use the legacy purple brand** (`#5c17e7` / `#130e21` from older landing prompts). Current identity is grayscale + sky accent.

---

## 1. Design identity

### Personality
Calm, paper-first, professional SaaS. Feels like a well-printed notebook: soft warm paper, near-black ink, thin hairlines, one cool accent used sparingly.

### Principles
| Principle | Practice |
|-----------|----------|
| Grayscale first | Most UI is ink / paper / line. Color is semantic or brand accent only. |
| Accent sparingly | Sky blue (`brand-primary`) for brand moments, nav underlines, soft button sheens — not full purple themes or neon glow. |
| Generous radius | Pills for actions; `lg`–`2xl` for surfaces; avoid sharp boxes. |
| Hairline borders | `1px` `#e7e7e7` / `#d4d4d4`; hover often darkens border to ink, not a heavy shadow. |
| Soft elevation | Prefer border + tiny lift over multi-layer drop shadows. |
| Display hierarchy | Titles use **Thmanyah Sans** at light–regular weight with tight tracking (LTR). Body stays quieter. |
| One job per screen | Landing sections and portal pages: one headline, short support, clear primary action. |
| Product as hero | Show CVs, cover letters, templates, device/document mockups — not stock people photography. |
| Motion with purpose | Short (150–200ms) ease-outs; orbit/marquee only where they sell the product; honor reduced motion. |

### Anti-patterns (avoid)
- Purple / indigo SaaS gradients, cream+terracotta “AI default”, broadsheet newspaper density
- Glow stacks, neon outlines, emoji as UI chrome
- Cards everywhere (especially in heroes); use surfaces only when interaction/grouping needs a container
- Accent blue as large filled backgrounds or full-screen themes
- Inter / Roboto / system as the *display* face

---

## 2. Brand assets

| Asset | Path (Laravel `public/`) | Use |
|-------|--------------------------|-----|
| Horizontal (dark UI) | `/images/logo-horizontal-white.png` | Dark hero, glass header |
| Horizontal (light UI) | `/images/logo-horizontal.png` | Portal, light chrome |
| Icon / mark | `/images/logo-icon.png` | App icon fallback, small mark |
| Favicon / square | `/images/cv-logo.png` | Favicon, store icon base |
| Auth / paper texture | `/images/cover-papers.jpg`, `/images/bg-paper.jpg` | Auth aside, atmospheric backgrounds |
| Objects collage | `/images/papers-objects.png` | Marketing / empty-state atmosphere |

**Logo rules**
- Prefer the horizontal lockup at ~24–28px height in chrome; mark alone only when space is tight.
- Never recolor the logo with filters; use the white or dark asset that matches the surface.
- Clear space: ≈ half the mark height on all sides.

---

## 3. Color system

### Neutrals (core UI)

| Token | Hex | Role |
|-------|-----|------|
| `ink` | `#0a0a0a` | Primary text, primary button fill, focus ring |
| `ink-2` | `#1f1f1f` | Primary hover, elevated dark |
| `ink-soft` | `#4a4a4a` | Secondary text, labels support |
| `muted` | `#8a8a8a` | Meta, captions, placeholders |
| `line` | `#e7e7e7` | Default borders, dividers |
| `line-2` | `#d4d4d4` | Stronger border (secondary buttons) |
| `paper` | `#fafaf9` | App background |
| `paper-2` | `#f4f4f2` | Nested surfaces, hover fills, secondary CTA fill |
| `paper-3` | `#ecece9` | Deeper nested / pressed |
| `white` | `#ffffff` | Cards, inputs, sidebar |
| `black` | `#000000` | Rare absolute black |

### Brand / accent

| Token | Hex | Role |
|-------|-----|------|
| `accent` / `brand-primary` | `#38bdf8` | Sky accent — CTAs sheen, active nav underline, brand highlights |
| `accent-soft` | `#e0f2fe` | Soft accent wash / selected chip |
| `brand-primary-dark` | `#0ea5e9` | Pressed / darker sky |
| `brand-midnight` | `#0c1222` | Deep navy for dark mock accents (not page chrome default) |

**Usage rule:** Primary solid buttons are **ink on paper text**, with a *subtle* top-to-bottom sky mix (~10%) in the fill — not a solid blue button. Accent blue is the “spot” and underline, not the whole control.

### Semantic

| Token | Hex | Soft |
|-------|-----|------|
| `success` | `#1f7a4f` | `#e6f5ec` |
| `warning` | `#b27318` | `#fff4d6` |
| `danger` | `#c0392b` | `#fdecea` |

### Dark marketing surfaces
Hero / auth aside use near-black (`#050505` / ink) with white/55% subtitle text, white/10–18% borders, and optional paper-texture photo at ~10% opacity under a dark gradient.

### Selection & focus
- Selection: ink background, paper text.
- Focus-visible: `2px` ink outline, `2px` offset (inputs also use ink border + soft black ring).

### Mobile token map (example)

```dart
// Flutter ColorScheme-oriented mapping
static const ink = Color(0xFF0A0A0A);
static const ink2 = Color(0xFF1F1F1F);
static const inkSoft = Color(0xFF4A4A4A);
static const muted = Color(0xFF8A8A8A);
static const line = Color(0xFFE7E7E7);
static const line2 = Color(0xFFD4D4D4);
static const paper = Color(0xFFFAFAF9);
static const paper2 = Color(0xFFF4F4F2);
static const paper3 = Color(0xFFECECE9);
static const brandPrimary = Color(0xFF38BDF8);
static const brandPrimaryDark = Color(0xFF0EA5E9);
static const brandMidnight = Color(0xFF0C1222);
static const accentSoft = Color(0xFFE0F2FE);
static const success = Color(0xFF1F7A4F);
static const successSoft = Color(0xFFE6F5EC);
static const warning = Color(0xFFB27318);
static const warningSoft = Color(0xFFFFF4D6);
static const danger = Color(0xFFC0392B);
static const dangerSoft = Color(0xFFFDECEA);
```

---

## 4. Typography

### Families
| Role | LTR | Arabic (`ar`) | Other RTL |
|------|-----|---------------|-----------|
| Display / titles | **Thmanyah Sans** | Thmanyah Sans | Thmanyah Sans for titles |
| Body / UI | **Inter Tight** (fallback Inter) | Thmanyah Sans | IBM Plex Sans Arabic / Cairo |
| Mono | JetBrains Mono | JetBrains Mono | JetBrains Mono |

Font files (web): `/fonts/thmanyah/thmanyahsans-{Light,Regular,Medium,Bold,Black}.woff2`  
Weights used: 300, 400, 500, 700, 900. Prefer **400** for large display titles (not heavy black).

### Scale (web → mobile)

| Style | Size | Line | Tracking (LTR) | Weight | Use |
|-------|------|------|----------------|--------|-----|
| Display 1 | clamp ~40–72px → mobile ~34–40 | 1.02 | −0.02em / −0.03em | 400 | Landing hero |
| Display 2 | ~32–52 → ~28–32 | 1.05 | −0.02em | 400 | Section titles |
| Display 3 | ~24–32 → ~22–26 | 1.15 | −0.02em | 400 | Card / page titles |
| Lede | ~17px | snug | default | 400 | Supporting sentence under title |
| Body | 15–16px | 1.45–1.6 | −0.005em body | 400 | Paragraphs, lists |
| Label | 12.8px (0.8rem) | — | wide | 500 | Field labels |
| Eyebrow | ~11.5px | — | widest + uppercase | 500 | Section kicker (+ optional hairline before) |
| Meta | 12–12.5px | — | slight wide | 400–500 | Timestamps, tags |

**RTL:** zero out negative letter-spacing on display and headings.

### Tone
Titles: confident, calm, not shouty. Body: clear and short. Avoid ALL CAPS except eyebrows/meta.

---

## 5. Radius, shadow, spacing

### Radius
| Token | Value | Use |
|-------|-------|-----|
| `sm` | 8px | Small chips, icon buttons inner |
| `md` | 12px | Inputs, list rows, compact cards |
| `lg` | 18px | Default `.surface` |
| `xl` | 24px | Feature panels, large cards |
| `2xl` | 32px | Hero frames, marketing shells |
| `pill` | 9999 | Buttons, tags, header bar, switches |

### Shadow
| Token | Value | Use |
|-------|-------|-----|
| `1` | `0 1px 2px rgba(0,0,0,0.04)` | Switch thumb, micro |
| `2` | `0 4px 12px -4px rgba(0,0,0,0.08)` | Hover lift |
| `3` | `0 12px 32px -16px rgba(0,0,0,0.16)` | Sidebar / modal depth |

### Spacing rhythm
- Page padding: 16–20px mobile, 32–40px desktop containers (`max-width` ~76rem).
- Section vertical: 64–96px on marketing; portal denser (12–24px gaps).
- Control padding: buttons `14–20px` × `12px`; inputs `~15px` × `11px`.
- Stack gap in forms/lists: 12–16px.

---

## 6. Components

### Buttons (pill)
All variants: pill radius, medium weight ~0.9rem, 180ms transitions, optional cursor “spotlight” radial using brand primary at low opacity (desktop hover; optional on mobile long-press).

| Variant | Fill | Text | Border | Notes |
|---------|------|------|--------|-------|
| Primary | Ink + soft sky top wash | Paper | Ink | Hover → `ink-2`, slight lift (−1px) |
| Secondary | White / paper-2 + soft sky wash | Ink | `line-2` | Hover border → ink |
| Ghost | Transparent | `ink-soft` | none | Hover → `paper-2` |
| Danger | Transparent | Danger | Danger | Hover → `danger-soft` fill |
| Link | none | Ink | underline | No pill chrome |
| On dark (hero register) | `paper-2` → white | Ink | none | Same sky wash |
| On dark (ghost AI) | white/8% | White | white/18% | |

Sizes: `sm` / `md` / `lg`; icon-only = square pill (~40px / 34px sm). Disabled: 50% opacity.

### Inputs
- White fill, `line` border, `md` radius.
- Focus: ink border + `0 0 0 3px rgba(0,0,0,0.08)`.
- Error: danger border + soft danger ring.
- Label above; hint muted; error danger 12px.

### Tags / chips
Pill; default ink fill + paper text; outline / soft / semantic variants exist. Keep compact (~11–12px type).

### Surfaces / cards
- Default: white + `line` + `lg` radius.
- Hover interactive: border → ink, translateY(−2px), `shadow-2`.
- Flat: `paper-2` background.
- Dark: ink fill, paper text, `xl` radius.
- Prefer list rows with hairline dividers over card grids when density matters (portal).

### Header (marketing)
Floating pill bar: dark translucent (`rgba(12,12,12,0.55)` → stronger when scrolled), blur 14px, white/10 border. Nav text white/70 → white; active link gets **brand-primary** 2px underline. CTA uses light secondary button treatment.

### Portal chrome
- Background: `paper` + very subtle black radial washes.
- Top bar: frosted paper (`paper` @ ~88% + blur).
- Sidebar: white, `line` edge, ink text; active link filled soft; danger logout in danger color.

### Auth
Split layout: dark textured aside (paper photo + dark + soft sky gradient) + light form on paper. Display title on aside; form titles use display font.

---

## 7. Motion

| Motion | Spec | Where |
|--------|------|-------|
| Control hover | 150–180ms ease-out; optional −1px Y | Buttons |
| Reveal on scroll | 700ms opacity + 8px Y | Landing sections |
| Hero orbit | ~50s linear infinite rotate | Landing hero ring |
| Spotlight | Radial follows pointer on buttons | Desktop |
| Switch | 180ms thumb / track | Forms |

**Reduced motion:** disable orbit and long transitions; keep UI usable instantly.

Mobile: prefer subtle fades and shared-element style transitions; skip continuous orbit unless performance is fine; no pointer spotlight.

---

## 8. Iconography & imagery

- Stroke icons, monochrome (ink / paper / muted); ~16px in buttons, 20–24px in nav.
- Imagery: template previews, CV/cover mockups, paper textures — product surface as visual anchor.
- Avoid decorative purple glows and stock lifestyle photos as the main idea.

---

## 9. Layout & UX patterns (mobile-first)

### App structure (suggested)
1. **Auth** — logo, short promise, email/password + Google; match web auth calm tone.
2. **Home / dashboard** — greeting, primary “Create CV”, recent CVs / cover letters, light stats (not a noisy KPI dashboard).
3. **Lists** — paper background, white rows or hairline list cards, trailing chevron, status in success/warning/muted.
4. **Editors** — section hub, clear primary Save, secondary Preview/PDF; sticky bottom bar on small screens.
5. **Templates** — gallery of previews; selection ring uses ink or soft accent, not loud color fills.
6. **Settings** — grouped lists on white surfaces; switches use ink when on.

### Hierarchy
- One primary CTA per view (ink pill).
- Secondary actions as secondary / ghost.
- Destructive always danger + confirm.

### Empty states
Short display title, one sentence, one primary CTA. Optional paper/objects illustration at low visual weight.

### Feedback
Toasts: quiet, ink/paper or semantic soft backgrounds. Errors inline on fields first.

### RTL
- Mirror padding, chevrons, sidebar edge.
- Arabic: Thmanyah for UI; no negative tracking on titles.
- Keep brand mark unflipped.

---

## 10. Accessibility

- Text contrast: ink on paper / paper on ink meet body contrast; muted only for non-essential meta.
- Focus always visible (ink ring).
- Hit targets ≥ 44×44 on mobile for icon buttons.
- Don’t rely on color alone for status (pair with label/icon).
- Respect system reduced-motion and dynamic type where platform allows (scale display styles carefully).

---

## 11. Implementation checklist (mobile)

- [ ] Port color + radius + type tokens into theme (`ThemeData` / design tokens package).
- [ ] Bundle Thmanyah Sans (+ Inter Tight or system for LTR body if licensing requires).
- [ ] Ship logo horizontal light/dark + icon.
- [ ] Primary button = ink pill + light sky sheen (gradient overlay), not solid `#38bdf8`.
- [ ] App scaffold background = `paper`; cards = `white` + `line`.
- [ ] Use pill buttons and `md` inputs consistently.
- [ ] Active tab / selected nav: ink text + optional sky underline or soft accent wash.
- [ ] Semantic colors only for success / warning / danger.
- [ ] Verify AR locale font + RTL layout.
- [ ] Align copy tone with web i18n (`frontend/locales`).

---

## 12. Reference files (web)

| Concern | Path |
|---------|------|
| Tokens | `frontend/app/assets/css/base.css` |
| Type | `frontend/app/assets/css/typography.css` |
| Buttons | `frontend/app/assets/css/buttons.css` |
| Forms | `frontend/app/assets/css/forms.css` |
| Surfaces | `frontend/app/assets/css/cards.css` |
| Header | `frontend/app/assets/css/header.css` |
| Auth | `frontend/app/assets/css/auth.css` |
| Portal | `frontend/app/assets/css/portal.css` |
| Landing hero | `frontend/app/assets/css/sections.css` + `HeroSection.vue` |
| Spotlight plugin | `frontend/app/plugins/btn-spot.client.ts` |

API / product flows for the mobile client: [mobile/README.md](./mobile/README.md).
