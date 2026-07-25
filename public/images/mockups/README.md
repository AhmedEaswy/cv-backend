# Mockup placeholders

These are **placeholder SVG mockups** for the landing page. They are sized
to mirror the layout of the inline CSS mockups used in
`resources/views/landing/index.blade.php`, so the page is visually complete
without any real product screenshots.

When real product screenshots are ready, replace these files with the same
filenames and the inline mockups can be swapped to `<img>` tags with no
layout change.

## Files

| File | Used in (section) | Recommended size |
| --- | --- | --- |
| `cv-modern.svg` | Hero (right sheet) + Templates grid (Modern card) | 600 × 800 |
| `cv-classic.svg` | Templates grid (Executive card) | 600 × 800 |
| `cv-minimalist.svg` | Templates grid (Minimalist card) | 600 × 800 |
| `cover-letter.svg` | Cover letter section | 600 × 800 |

## How to swap

1. Drop the real screenshot at the same path (keep `.svg` or change to `.png`/`.webp`).
2. In `resources/views/landing/index.blade.php`, replace the inline
   `<div class="sheet …">` block with:
   ```html
   <img src="{{ asset('images/mockups/cv-modern.svg') }}"
        alt="{{ __('landing.template_modern_name') }}"
        class="w-full h-auto rounded-md">
   ```
3. Rebuild assets if needed (`npm run build`) — no PHP cache clear required.

## Notes

- Keep the **violet `#5c17e7` + midnight `#130e21`** brand palette in the
  replacement art so the page keeps its identity.
- Mockups should remain paper-shaped (A4-ish 3:4 ratio) so the layout still
  breathes.
