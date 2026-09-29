# Memory

## Project Overview
See @README.md for project overview and @package.json for available npm/pnpm commands for this project.

## Code Style Guidelines
- Use descriptive variable names
- Follow existing patterns in the codebase
- Extract complex conditions into meaningful boolean variables

## Architecture Notes
- User-facing UI (landing, auth, portal) is a **Nuxt 3** app in `frontend/`.
- Laravel owns the API (`/api/v1`), Filament admin (`/admin`), and public profile previews (`/u/{slug}`, etc.).
- On Laragon, `scripts/laragon/cv.test.conf` proxies `cv.test` to Nuxt (:3000) while Laravel routes stay on PHP.

## Translations (required for every edit)
- Supported locales: `en`, `ar`, `tr`, `es`, `fr`, `de`, `ur`. The source of truth is `resources/lang/<locale>.json` (flat keys, Laravel `:name` placeholders). `frontend/locales/*.json` is generated from it — never edit it by hand.
- Never hardcode user-visible text (labels, buttons, headings, alts, aria-labels, toasts, validation/flash messages, emails, sample/mock copy) in Vue, Blade, PHP, or Filament. Use `t('key')` in Nuxt and `__('key')` in Laravel.
- Every new or changed English string must be added to **all 7** locale files in the same change, with a real translation — never copy the English value into non-English files. Only brand names, acronyms, and native language names may stay identical.
- If an English value changes, update the translations of that key in every locale too.
- Before finishing any edit, run `cd frontend && pnpm i18n:check` (must report 0 untranslated for every locale), then `pnpm sync:locales`.
- For bulk work: `pnpm i18n:extract` writes the untranslated strings to `resources/lang/patches/pending/source.json`; put translations in `pending/<locale>.json` (same ids), run `pnpm i18n:apply`, then delete the `pending/` folder. Values deliberately kept identical are recorded in `resources/lang/patches/identical-allowlist.json`.

## Common Workflows
- **Local dev (Laragon):** install `scripts/laragon/cv.test.conf` into Laragon nginx, reload nginx, then run `cd frontend && pnpm dev`. Open https://cv.test/
- **Local dev (artisan):** `composer dev` starts Laravel, queue, Vite, and Nuxt together; use http://localhost:3000 for the UI.
