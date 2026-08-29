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

## Common Workflows
- **Local dev (Laragon):** install `scripts/laragon/cv.test.conf` into Laragon nginx, reload nginx, then run `cd frontend && pnpm dev`. Open https://cv.test/
- **Local dev (artisan):** `composer dev` starts Laravel, queue, Vite, and Nuxt together; use http://localhost:3000 for the UI.
