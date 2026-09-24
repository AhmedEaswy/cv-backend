# CV Builder

AI-ready CV, cover letter, and public profile builder. Laravel powers the API, admin, and PDF rendering; Nuxt 3 serves the landing page, auth, and user portal.

## Features

- **CVs** — multi-template resumes, section editing, LinkedIn import, PDF export
- **Cover letters** — templates, print/PDF, CRUD
- **Public profiles** — shareable `/u/{slug}` pages with contact form
- **ATS check** — score CVs (paste or upload)
- **Auth** — email verification, password reset; Google, LinkedIn, Apple
- **i18n** — `en`, `ar`, `de`, `es`, `fr`, `tr`, `ur` (RTL where needed)
- **Admin** — Filament at `/admin`
- **MCP / agents** — Laravel MCP tools plus `/.well-known/mcp.json`, `/openapi.json`, `/llms.txt`

## Stack

| Layer | Tech |
|-------|------|
| API & admin | Laravel 12, Sanctum, Filament 4 |
| Web UI | Nuxt 3, Pinia, sidebase Auth.js, Tailwind 4 |
| PDF | Spatie Laravel PDF (Browsershot / Chromium) |
| Mobile | REST `/api/v1` + Postman collection in `docs/` |

## Architecture

```
Browser ──► Nuxt (:3000)     landing, portal, auth UI
                │
                └──► Laravel (/api/v1, /admin, /u/*, PDF, MCP)
```

On Laragon, `scripts/laragon/cv.test.conf` proxies most of `cv.test` to Nuxt and keeps Laravel paths (`/api`, `/admin`, `/storage`, `/u`, OAuth, agent discovery) on PHP.

## Requirements

- PHP 8.2+, Composer
- Node.js 20+, pnpm (frontend), npm (root Vite)
- MySQL (or SQLite for quick local use)
- Chromium available for PDF generation (Puppeteer is a root dependency)

## Quick start

### 1. Backend

```bash
composer install
cp .env.example .env
php artisan key:generate
# Configure DB_* (and FRONTEND_URL) in .env
php artisan migrate --seed
```

### 2. Frontend

```bash
cd frontend
cp .env.example .env
pnpm install
```

Set `NUXT_PUBLIC_LARAVEL_URL` to your Laravel origin (`https://cv.test` with Laragon, or `http://localhost:8000` with `artisan serve`).

### 3. Run

**All-in-one** (Laravel + queue + Vite + Nuxt):

```bash
composer dev
```

UI: [http://localhost:3000](http://localhost:3000)

**Laragon** (recommended on Windows):

1. Copy `scripts/laragon/cv.test.conf` → `E:/laragon/etc/nginx/sites-enabled/cv.test.conf`
2. Remove conflicting `auto.cv.test.conf` if present; reload Nginx
3. `cd frontend && pnpm dev`
4. Open [https://cv.test](https://cv.test)

## Environment

### Laravel (`.env`)

| Variable | Purpose |
|----------|---------|
| `APP_URL` | Laravel origin |
| `FRONTEND_URL` | Nuxt origin (CORS / cookies) |
| `DB_*` | Database |
| `GOOGLE_*` / `LINKEDIN_*` / `APPLE_*` | Social OAuth |
| `*_AUTH_ENABLED` | Toggle LinkedIn / Apple |

### Nuxt (`frontend/.env`)

| Variable | Purpose |
|----------|---------|
| `NUXT_PUBLIC_LARAVEL_URL` | API base |
| `NUXT_AUTH_ORIGIN` | Auth.js origin (`…/api/auth`) |
| `NUXT_GOOGLE_CLIENT_ID` / `SECRET` | Google via Auth.js |
| `NUXT_PUBLIC_LINKEDIN_AUTH_ENABLED` / `APPLE_*` | Show/hide social buttons |

## Useful commands

```bash
# Tests
composer test

# Frontend typecheck / build
cd frontend && pnpm typecheck
cd frontend && pnpm build

# Capture template preview images
php artisan templates:capture-previews   # if registered
```

## API & docs

| Doc | Path |
|-----|------|
| Developer overview | [docs/index.md](docs/index.md) |
| Mobile / API | [docs/mobile/README.md](docs/mobile/README.md) |
| Postman | [docs/CV_Mobile_API.postman_collection.json](docs/CV_Mobile_API.postman_collection.json) |
| UI / UX | [docs/ui-ux-guide.md](docs/ui-ux-guide.md) |
| Landing meta-prompt | [docs/landing-meta-prompt.md](docs/landing-meta-prompt.md) |

API prefix: `/api/v1` (auth, CVs, cover letters, public profiles, ATS, portal stats, AI settings, agent tokens).

## Project layout

```
app/                 Laravel domain, API, Filament, MCP tools
frontend/            Nuxt 3 app (landing + portal)
resources/views/     Blade CV / cover letter / public profile templates
docs/                API, mobile, UX docs
scripts/laragon/     nginx vhost for cv.test
tests/               PHPUnit feature/unit tests
```

## Agent notes

See [AGENTS.md](AGENTS.md) for architecture shortcuts used by coding agents.
