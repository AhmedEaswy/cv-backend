# Memory

## Project Overview
See @README.md for project overview and @package.json for available npm/pnpm commands for this project.

## Code Style Guidelines
- Use descriptive variable names
- Follow existing patterns in the codebase
- Extract complex conditions into meaningful boolean variables

## Architecture Notes
Add important architectural decisions and patterns here.

## Common Workflows
Document frequently used workflows and commands here.

## Cursor Cloud specific instructions

This is a Laravel 12 (PHP 8.3) app: a CV/Resume + Cover Letter builder. It exposes a REST API under `/api/v1` (Sanctum bearer tokens), a public marketing/preview web UI, and a Filament v4 admin panel at `/admin`. PDFs are rendered server-side by `spatie/laravel-pdf` (Browsershot → headless Chrome via Node/Puppeteer). The dependency-refresh step (`composer install`, `npm install`) is handled by the startup update script — do not duplicate it here.

- Database is SQLite in this environment. `.env` sets `DB_CONNECTION=sqlite` and the DB lives at `database/database.sqlite` (git-ignored). The migrated + seeded DB is part of the VM snapshot, so migrations/seeders are NOT in the update script. To reset from scratch: `php artisan migrate:fresh --seed`.
- Seeders only create local users when `APP_ENV=local` (see `DatabaseSeeder` → `LocalFakeDataSeeder`). Seeded logins: admin `admin@app.com` / `123456789` (Filament admin, requires `type=admin`), regular user `user@app.com` / `123456789`. `TemplateSeeder`/`CoverLetterTemplateSeeder` seed the CV/cover-letter templates that PDF generation depends on.
- Run the app: `php artisan serve --host=0.0.0.0 --port=8000`. Frontend assets are pre-built via `npm run build` (Vite) into `public/build`, so the server works without a running Vite dev server. For live HMR use `npm run dev`, or run everything at once with `composer dev` (serve + queue:listen + vite via concurrently). A queue worker is optional — there are no dispatched jobs and PDF generation is synchronous.
- PDF generation (core feature) needs Node + Chrome. `.env` configures `LARAVEL_PDF_NODE_BINARY` (nvm node), `LARAVEL_PDF_CHROME_PATH` (Puppeteer's downloaded Chrome under `~/.cache/puppeteer/chrome/<version>/chrome-linux64/chrome`), and `LARAVEL_PDF_NO_SANDBOX=true` (required — Chrome cannot sandbox in this container). Gotcha: if `puppeteer` is upgraded the Chrome version directory changes, so update `LARAVEL_PDF_CHROME_PATH` to match the new path. `php artisan storage:link` is required for PDF-URL mode (the `/cvs/print` endpoint returns `http://localhost/storage/cvs/...`).
- Tests: `php artisan test` (PHPUnit; `phpunit.xml` forces an in-memory SQLite DB, so tests don't touch `database/database.sqlite`). Lint: `./vendor/bin/pint` to format, `./vendor/bin/pint --test` to check. Note: the repo currently has many pre-existing Pint style deviations, so `pint --test` reports failures on unmodified files — that is expected, not caused by your changes.
