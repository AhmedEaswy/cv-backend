# Mobile App Integration

Guides and Postman collection for integrating a native mobile client (iOS / Android / Flutter / React Native) with the CV Builder Laravel API.

## Contents

| Doc | Purpose |
|-----|---------|
| [Getting started](./getting-started.md) | Base URL, headers, auth, first requests |
| [Authentication](./authentication.md) | Email/password, Google Sign-In, password reset, tokens |
| [API reference](./api-reference.md) | Every `/api/v1` endpoint with auth, body, and response notes |
| [Data models](./data-models.md) | Response envelope, CV / cover letter / public profile `user_data` |
| [Mobile workflows](./workflows.md) | Recommended screen → API flows |
| [UI / UX guide](../ui-ux-guide.md) | Brand identity, colors, type, components for the app UI |

## Postman

Import: [`../CV_Mobile_API.postman_collection.json`](../CV_Mobile_API.postman_collection.json)

Set collection variable `base_url`:

- Laragon: `https://cv.test`
- `php artisan serve`: `http://localhost:8000`

Run **POST Login** or **POST Register** — `auth_token` is saved automatically and injected as `Authorization: Bearer …`.

## Stack summary

- **API:** Laravel, path-versioned at `/api/v1`
- **Auth:** Laravel Sanctum personal access tokens (Bearer) — use this on mobile, not cookies
- **UI web app:** Nuxt 3 in `frontend/` (not required for mobile)
- **Admin:** Filament at `/admin` (not for mobile)
- **Billing:** none

## Product surfaces the API covers

1. Auth & account
2. CV / resume CRUD + PDF print
3. Cover letter CRUD + PDF print
4. Template catalogs (CV, cover letter, public profile)
5. ATS scoring (structured JSON or PDF upload)
6. Public profile (one per user) + public web URL `/u/{slug}`
7. Portal dashboard stats
8. Per-user AI provider settings
9. Agent / MCP tokens (optional)
10. Analytics click beacon (store badges, etc.)
