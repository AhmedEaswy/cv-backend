# Landing Page Meta-Prompt

Meta-prompt for generating one-page landing page prompts for our **CV + Cover Letter creator**. Paste into MiniMax-M3 (or any LLM); feed the 3-paragraph output into Gemini / V0 for HTML.

**Assumptions:** multi-language audience (EN / AR RTL / TR); primary CTA “Create your CV”; features — CV templates, cover letters, PDF export, multi-language, ATS-friendly; target — Laravel Blade at `resources/views/landing/index.blade.php`; brand-biased style list.

**Brand:** Primary `#5c17e7` · Secondary `#130e21` · Logos: `public/images/cv-logo.png`, `logo-horizontal.png`, `logo-icon.png`

**Imagery:** Use **placeholder mockups** (phone frames, CV/cover-letter paper previews built with HTML/CSS/SVG). Real app screenshots will replace them later — keep image slots easy to swap (`src`, `background-image`, or Blade `asset()` paths).

---

## Meta-prompt (copy below)

```
You are a senior product designer and front-end creative director. Generate a
ONE-PAGE LANDING PAGE creation prompt for our product — a CV and Cover Letter
creator mobile + web app — using a RANDOMLY SELECTED design style.

PRODUCT CONTEXT (use this verbatim in the generated prompt):
- Product name: our CV + Cover Letter creator (refer to it as a single,
  cohesive brand; do not invent a specific brand name unless the style calls
  for it, in which case choose something calm and confident such as "Vitae"
  or "Linea").
- Core promise: build a professional, ATS-friendly CV and matching cover
  letter in minutes, export to PDF, in multiple languages.
- Brand identity:
    Primary:   #5c17e7 (vivid violet)
    Secondary: #130e21 (deep midnight)
    Neutrals:  white, soft grays, one warm accent at your discretion
    Radii:     generous, ~16px, soft
    Type:      geometric sans for LTR (Inter / General Sans family);
               humanist sans for RTL (Cairo family); strong hierarchy,
               large hero display, comfortable body
- Surface features the page must hint at without enumerating them as a
  feature list:
    * Curated CV template gallery (modern, classic, ATS-friendly)
    * Cover letter generator with matching visual language
    * One-tap PDF export / print-ready output
    * Multi-language with full RTL support (English, Arabic, Turkish)
    * Designed mobile-first, equally beautiful on desktop
- Logo assets are available as horizontal lockup, square mark, and icon —
  the landing page must use them naturally in header and footer.
- Imagery / mockups (IMPORTANT — no real product screenshots yet):
    * Use PLACEHOLDER MOCKUPS only until real live images are supplied later.
    * Hero and feature sections should show stylized device frames (phone /
      tablet) and paper-style CV + cover letter previews built with HTML,
      CSS, and/or SVG — not stock photos of random people or fake UI from
      other products.
    * CV mockups: A4/letter-shaped white sheets with plausible skeleton
      content (name bar, section lines, skill chips) using brand colors;
      vary 2–3 template looks (classic, modern, ATS-friendly).
    * Cover letter mockups: matching letter layouts beside or behind CVs.
    * Prefer CSS/SVG mockups over external image URLs. If an <img> is used,
      point it at a clearly named placeholder path (e.g.
      /images/mockups/cv-preview-1.svg or a gray/violet CSS block) so real
      screenshots can replace them later without redesigning the layout.
    * Do not rely on Lorem-heavy stock photography; the product surface
      (documents + device frames) is the visual hero.
- Call to action: primary CTA "Create your CV" (or "Build your CV" /
  "Start your CV" — pick the phrasing that best fits the chosen style's
  emotional register); secondary CTA "See templates" or "Browse examples".
- Color rule: the brand purple #5c17e7 and midnight #130e21 must always be
  visible — either as the dominant palette or as the anchor against which
  the style's signature palette plays. Do not invent a different primary.

STYLE SELECTION:
Pick ONE style at random from this curated list (or, if you identify a more
suitable professional style, use it and explain why):
Glassmorphism, Gradient Modern, Dark Mode First, Luxury Minimal,
Tech Forward, Organic/Fluid, Editorial, Metropolitan, Neumorphic,
Material, Swiss/International, Typography First, Japandi, Scandinavian,
Monochromatic, Kinetic. Avoid Neobrutalist and Bauhaus — they fight the
brand's calm, premium tone. Use a real random method; do not default.

OUTPUT FORMAT — exactly THREE paragraphs, no more, no less:

Paragraph 1 — Concept & first impression
State the chosen style and ask the AI to conceive an innovative
single-page narrative for our CV + Cover Letter creator. Describe the core
emotional qualities and feeling this style evokes. What mood should a
visitor experience as they arrive? How should the visual hierarchy and
flow make them feel as they scroll through this single cohesive page?
Reference the brand colors (#5c17e7 primary, #130e21 secondary) and the
product surface (CV + cover letter, templates, PDF export, multi-language
with RTL) as the substance the style must dress. Require placeholder
device + document mockups (HTML/CSS/SVG) as the visual hero — not real
screenshots yet — with clear swap-in slots for live images later.
Include a note to incorporate colorful gradients and accents as
appropriate to enhance the design's emotional impact while keeping the
brand violet/midnight as anchor.

Paragraph 2 — Design philosophy through emotion
Explain the design philosophy through the lens of emotion and user
experience for this specific product. How should typography feel —
authoritative yet welcoming, or cutting-edge yet trustworthy? What
sensation should interactions and animations create — smooth and
premium, snappy and confident, gentle and organic? Describe how the
single-page journey should emotionally progress from first impression
through final call-to-action, creating a complete narrative arc in one
scrolling experience that culminates in "Create your CV" / "See
templates". The arc should move visitors from self-doubt about job
applications to quiet confidence that this tool will help them.

Paragraph 3 — Abstract references & quality bar
Provide abstract reference points that capture this aesthetic's essence —
think about the feeling of certain types of spaces (a quiet architect's
studio, a modern art museum lobby, a premium co-working space at dawn),
cultural movements, artistic periods, architectural styles, or design
philosophies that embody this aesthetic. Reference the emotional
qualities of premium experiences, sophisticated environments, or refined
craftsmanship that should inspire the design. Explain how these abstract
references should influence the emotional quality and visual
sophistication of the final single-page design. Close with a quality
bar: this page should feel like the cover of a well-designed book
about someone's career — calm, considered, confident — not like a
generic SaaS marketing page. Do NOT name specific brands, platforms,
or products.

The generated prompt must emphasize this is ONE COHESIVE LANDING PAGE with
a single scrolling experience. Focus on feeling, atmosphere, and abstract
quality references rather than technical details. Keep all references
conceptual and high-level to allow for maximum creative interpretation.
The product is a CV + Cover Letter creator — every paragraph must serve
that single idea.
```

---

## Workflow

1. Copy the meta-prompt above into MiniMax-M3.
2. Paste the 3-paragraph output into Google AI Studio (Gemini) or V0.
3. Drop generated HTML into `resources/views/landing/index.blade.php` (route outside `/admin`), or `public/landing/` for standalone HTML.
4. When real screenshots are ready, replace placeholder mockup paths under `public/images/mockups/` (or swap CSS/SVG blocks) without changing layout.
