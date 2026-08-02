# CV API Collection Documentation

Complete documentation for every endpoint in the **CV Builder API** Postman collection.

> **Last updated:** 2026-08-02 — ATS Checker, Public Profiles, Analytics click.

## Table of Contents

- [Collection Overview](#collection-overview)
- [Getting Started](#getting-started)
- [Authentication](#authentication)
- [Response Envelope](#response-envelope)
- [Folders](#folders)
  - [1. Auth](#1-auth)
  - [2. Social Auth](#2-social-auth)
  - [3. CVs](#3-cvs)
  - [4. Cover Letters](#4-cover-letters)
  - [5. Templates](#5-templates)
  - [6. ATS Checker](#6-ats-checker)
  - [7. Public Profiles](#7-public-profiles)
  - [8. Analytics](#8-analytics)
- [Error Responses](#error-responses)

---

## Collection Overview

**Collection name:** `CV Builder API`
**Base URL:** `{{base_url}}` (default `http://localhost:8000`)
**API version:** `v1`
**File:** `CV_API_Collection.postman_collection.json`

### Collection Variables

| Variable | Default | Notes |
|----------|---------|-------|
| `base_url` | `http://localhost:8000` | Base URL for every request. |
| `auth_token` | `""` | Bearer token. Auto-stored after `POST Register` / `POST Login`. |
| `cv_id` | `""` | Auto-stored after `POST Create CV`. |
| `cover_letter_id` | `""` | Auto-stored after `POST Create Cover Letter`. |
| `template_id` | `1` | Active CV template ID. Get a fresh one from `GET CV Templates`. |
| `cover_letter_template_id` | `1` | Active cover-letter template ID. Get a fresh one from `GET List Cover Letter Templates`. |
| `public_profile_template_id` | `1` | Active public-profile template ID. From `GET Public Profile Templates`. |
| `last_pdf_url` | `""` | Auto-stored after any `/print` request. |

---

## Getting Started

1. Import `CV_API_Collection.postman_collection.json` into Postman.
2. Set the `base_url` variable to your environment (e.g. `http://localhost:8000` or your staging URL).
3. Run `POST Login` (or `POST Register`) — the token is auto-saved to `auth_token` and added to every subsequent request as `Authorization: Bearer {auth_token}`.

---

## Authentication

All authenticated endpoints use **Laravel Sanctum** Bearer tokens. The collection's pre-request script automatically appends:

```
Authorization: Bearer {auth_token}
```

when `auth_token` is set. The test script extracts the token from the `result.token` field of `Register` / `Login` responses.

---

## Response Envelope

Success:

```json
{
  "success": true,
  "message": "...",
  "result": {}
}
```

Error:

```json
{
  "success": false,
  "message": "...",
  "code": 400,
  "errors": null
}
```

> Print endpoints return `{ "result": { "url": "..." } }` — they **do not** return a raw PDF body. Open `result.url` in a browser or download manager to grab the file.

---

## Folders

## 1. Auth

User authentication, account management, and password recovery.

| Method | Endpoint | Auth | Notes |
|--------|----------|------|-------|
| `POST` | `/api/v1/auth/register` | — | Returns user + `result.token`. |
| `POST` | `/api/v1/auth/login` | — | Returns user + `result.token`. |
| `POST` | `/api/v1/auth/logout` | Required | Invalidates the current token. |
| `GET` | `/api/v1/auth/me` | Required | Current user profile. |
| `POST` | `/api/v1/auth/forgot-password` | — | Sends reset email. |
| `POST` | `/api/v1/auth/reset-token` | — | Validates a reset token. |
| `POST` | `/api/v1/auth/reset-password` | — | Resets password with token. |

### Register

```json
POST /api/v1/auth/register
Content-Type: application/json
Accept: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password",
  "password_confirmation": "password"
}
```

### Login

```json
POST /api/v1/auth/login
Content-Type: application/json
Accept: application/json

{
  "email": "user@app.com",
  "password": "123456789"
}
```

The `result.token` is auto-extracted by the test script and stored in `auth_token`.

### Forgot / Reset Password

```json
POST /api/v1/auth/forgot-password   { "email": "..." }
POST /api/v1/auth/reset-token       { "email": "...", "token": "..." }
POST /api/v1/auth/reset-password    { "email": "...", "token": "...", "password": "...", "password_confirmation": "..." }
```

---

## 2. Social Auth

OAuth redirect and callback endpoints. The redirect returns the provider's auth URL — open it in a browser to grant access. The callback exchanges the `code` for a Sanctum token.

| Method | Endpoint | Auth | Notes |
|--------|----------|------|-------|
| `GET` | `/api/v1/auth/google/redirect` | — | Returns the Google auth URL. |
| `GET` | `/api/v1/auth/google/callback` | — | Exchanges `code` for a token. |
| `GET` | `/api/v1/auth/linkedin/redirect` | — | Returns the LinkedIn auth URL. |
| `GET` | `/api/v1/auth/linkedin/callback` | — | Exchanges `code` for a token. |

In Postman, copy the `code` and `state` from the redirect URL and run the matching callback request manually.

---

## 3. CVs

CV / Resume management: full CRUD plus PDF print. Some endpoints are public (create/print) so unauthenticated users can build a CV; the rest require auth and return only the caller's CVs.

| Method | Endpoint | Auth | Notes |
|--------|----------|------|-------|
| `GET` | `/api/v1/cvs?language=en` | Required | List my CVs (optional `language` filter). |
| `GET` | `/api/v1/cvs/{id}` | Required | Get one of my CVs. |
| `POST` | `/api/v1/cvs` | Optional | Create a CV. Unauth + `template_id` → returns `result.url` PDF. |
| `PUT` | `/api/v1/cvs/{id}` | Required | Update a CV I own. |
| `DELETE` | `/api/v1/cvs/{id}` | Required | Soft delete. |
| `POST` | `/api/v1/cvs/print` | Optional | Generate PDF. Returns `{ result: { url } }`. |

### Create CV (Authenticated → profile)

```json
POST /api/v1/cvs
Authorization: Bearer {auth_token}
Content-Type: application/json

{
  "name": "My Professional CV",
  "language": "en",
  "sections_order": ["Personal Information", "Skills", "Education", "Experience", "Projects", "Languages", "Interests"],
  "user_data": {
    "firstName": "John",
    "lastName": "Doe",
    "jobTitle": "Senior Flutter Developer",
    "email": "john.doe@example.com",
    "address": "123 Tech Street, San Francisco, CA 94105, USA",
    "portfolioUrl": "https://johndoe.dev",
    "phone": "+1-555-123-4567",
    "summary": "Experienced Flutter developer with 5+ years...",
    "birthdate": "1990-05-15",
    "skills": [ { "name": "Flutter" }, { "name": "Dart" } ],
    "educations": [
      { "institution": "UC Berkeley", "degree": "BSc", "fieldOfStudy": "CS", "from": "2010-09", "to": "2014-06" }
    ],
    "experiences": [
      { "position": "Senior Flutter Dev", "company": "Tech Innovations", "from": "2021-03", "to": null, "current": true }
    ],
    "projects": [
      { "title": "E-Commerce App", "technologies": "Flutter, Dart, Firebase", "from": "2022-01", "to": "2022-12" }
    ],
    "languages": [ { "name": "English", "proficiencyLevel": 5 } ],
    "interests": [ { "name": "Open Source" } ]
  }
}
```

`user_data` is mapped server-side into the relational profile structure (info, educations, experiences, etc.). `result.id` is auto-stored as `cv_id`.

### Create CV (Unauthenticated → PDF URL)

If you send `template_id` **without** a Bearer token, the API still creates a profile, then returns the generated PDF URL:

```json
POST /api/v1/cvs
Content-Type: application/json

{
  "name": "My CV",
  "language": "en",
  "template_id": 1,
  "user_data": { "firstName": "John", "lastName": "Doe", "jobTitle": "Developer", "email": "john@example.com" }
}
```

Response:

```json
{ "success": true, "message": "...", "result": { "url": "http://localhost:8000/storage/cvs/abc.pdf" } }
```

### Update CV

```json
PUT /api/v1/cvs/{cv_id}
Authorization: Bearer {auth_token}
Content-Type: application/json

{
  "name": "Updated CV Name",
  "language": "ar",
  "user_data": { "firstName": "John", "lastName": "Doe Updated" }
}
```

All fields optional. Only the fields you include are written.

### Print CV

Two options, both return JSON `{ result: { url } }`:

**Option A — from a saved profile**

```json
POST /api/v1/cvs/print
{ "profile_id": 1, "template_id": 1 }
```

**Option B — ad-hoc data**

```json
POST /api/v1/cvs/print
{
  "template_id": 1,
  "name": "My CV",
  "language": "en",
  "user_data": { "firstName": "John", "lastName": "Doe", "jobTitle": "Developer", "email": "john@example.com" }
}
```

The response body **is not a PDF** — it is JSON. Open `result.url` in a browser / `url_launcher` to download the file. The same URL is auto-saved to the `last_pdf_url` variable.

---

## 4. Cover Letters

Full cover-letter lifecycle: templates, CRUD, JSON-URL print, and a web HTML preview. Public routes (templates, create, print) are accessible without auth; the rest require the owner.

| Method | Endpoint | Auth | Notes |
|--------|----------|------|-------|
| `GET` | `/api/v1/cover-letters/templates` | — | Active templates. `preview` is a full URL. |
| `GET` | `/api/v1/cover-letters?language=en` | Required | List my cover letters. |
| `GET` | `/api/v1/cover-letters/{id}` | Required | Get one of my cover letters. |
| `POST` | `/api/v1/cover-letters` | Optional* | Create a cover letter. |
| `PUT` | `/api/v1/cover-letters/{id}` | Required | Update a cover letter I own. |
| `DELETE` | `/api/v1/cover-letters/{id}` | Required | Soft delete. |
| `POST` | `/api/v1/cover-letters/print` | Optional* | Returns `{ result: { url } }`. |
| `GET` | `/cover-letter/{id}?template_id=` | — | Web HTML preview (no `/api` prefix). |

\* Public create/print still respect an authenticated user when a token is sent — `user_id` is taken from the token if present.

### List Cover Letter Templates

```http
GET /api/v1/cover-letters/templates
```

```json
{
  "success": true,
  "message": "...",
  "result": [
    { "id": 1, "name": "ats-classic",    "preview": "http://localhost:8000/storage/cover-letter-templates/ats-classic.svg", "description": "ATS-friendly...",  "is_default": true  },
    { "id": 2, "name": "professional",   "preview": "http://localhost:8000/storage/cover-letter-templates/professional.svg", "description": "Professional...", "is_default": false }
  ]
}
```

`preview` is an absolute URL — load it directly in `<img src="...">` or an `Image.network` widget. Treat `null` as "no image".

### `user_data` Shape

Used on create, update, and the ad-hoc print variant.

| Field | Type | Notes |
|-------|------|-------|
| `firstName` | string | Required on print when sending `user_data`. |
| `lastName` | string | Required on print when sending `user_data`. |
| `email` | string | |
| `phone` | string \| null | |
| `address` | string \| null | |
| `jobTitle` | string | Sender role / title. |
| `companyName` | string \| null | |
| `recipientName` | string | |
| `recipientTitle` | string \| null | |
| `recipientCompany` | string \| null | |
| `subject` | string | |
| `body` | string | Letter body (HTML/text as stored). |
| `closing` | string \| null | e.g. `"Sincerely"`. |
| `experiences` | array | Passed through as-is. |

### Create Cover Letter

```json
POST /api/v1/cover-letters
Content-Type: application/json

{
  "name": "Application – Acme",
  "language": "en",
  "cover_letter_template_id": 1,
  "sections_order": ["info", "body"],
  "user_data": {
    "firstName": "John",
    "lastName": "Doe",
    "email": "john@example.com",
    "phone": "+1-555-0100",
    "address": "City, Country",
    "jobTitle": "Software Engineer",
    "companyName": "",
    "recipientName": "Hiring Manager",
    "recipientTitle": "HR Lead",
    "recipientCompany": "Acme Inc.",
    "subject": "Application for Software Engineer",
    "body": "I am writing to apply for the Software Engineer role...",
    "closing": "Sincerely",
    "experiences": []
  }
}
```

Top-level fields:

| Field | Required | Rules |
|-------|----------|-------|
| `name` | Yes | string, max 255 |
| `language` | No | `en` \| `ar` \| `tr` (default `en`) |
| `cover_letter_template_id` | No | Must exist in `cover_letter_templates` |
| `sections_order` | No | string[] |
| `user_id` | No | Only if creating without a Bearer token |
| `user_data` | No | See table above |

Returns `201 Created`. The new `result.id` is auto-stored as `cover_letter_id`.

### List / Show / Update / Delete

```http
GET    /api/v1/cover-letters?language=en
GET    /api/v1/cover-letters/{id}
PUT    /api/v1/cover-letters/{id}     # same shape as create, all fields optional
DELETE /api/v1/cover-letters/{id}     # soft delete
```

Ownership: only the owner can show/update/delete. Other users get `404`.

### Print Cover Letter — JSON `{url}` response

> **Behaviour change (2026-07-25):** the response body is **JSON**, not a raw PDF. Open `result.url` to download.

**Option A — from an existing record**

```json
POST /api/v1/cover-letters/print
{ "cover_letter_id": 1, "template_id": 1 }
```

**Option B — ad-hoc data (no saved letter yet)**

```json
POST /api/v1/cover-letters/print
{
  "template_id": 1,
  "name": "Draft Cover Letter",
  "language": "en",
  "user_data": {
    "firstName": "John",
    "lastName": "Doe",
    "email": "john@example.com",
    "jobTitle": "Software Engineer",
    "subject": "Application for Software Engineer",
    "body": "I am writing to apply...",
    "closing": "Sincerely"
  }
}
```

Response:

```json
{ "success": true, "message": "...", "result": { "url": "http://localhost:8000/storage/cover-letters/cl_xxxx.pdf" } }
```

`user_data` must contain at least `firstName` + `lastName` when no `cover_letter_id` is supplied. If the letter belongs to another user → `404`. Inactive / missing template → `404`.

### HTML Preview (WebView / iframe)

Useful for live preview before or after save. **No `/api` prefix** — it's a web route (`routes/web.php` → `CoverLetterPreviewController@preview`).

```http
GET /cover-letter/{id}?template_id={optional}
```

- Uses the saved `cover_letter_template_id` when `template_id` is omitted.
- Falls back to the default active template, then any active template.
- Locale follows the cover letter's `language` (`en` / `ar` / `tr`).

Example: `http://localhost:8000/cover-letter/12?template_id=1`

---

## 5. Templates

| Method | Endpoint | Auth | Notes |
|--------|----------|------|-------|
| `GET` | `/api/v1/shares/templates` | — | Public. All active CV templates. |

```http
GET /api/v1/shares/templates
```

Returns an array of active CV templates with `id`, `name`, `type`, `preview_url`, etc. Use the `id` as `template_id` when calling `POST /cvs/print`.

> Cover-letter templates live under `/api/v1/cover-letters/templates` (see **4. Cover Letters** above) and are kept in the Cover Letters folder of the collection for grouping.

---

## 6. ATS Checker

Rule-based scoring (no LLM). Public endpoints.

| Method | Endpoint | Auth | Notes |
|--------|----------|------|-------|
| `POST` | `/api/v1/cvs/ats-check` | — | Score `user_data` or `profile_id`. Optional `job_description`. |
| `POST` | `/api/v1/cvs/ats-check/upload` | — | Multipart PDF upload (`file`, max 5MB). Optional `job_description`. |

### Structured check

```http
POST /api/v1/cvs/ats-check
Content-Type: application/json
```

```json
{
  "language": "en",
  "job_description": "Optional JD text for keyword / fit score",
  "user_data": { "...same shape as Create CV..." }
}
```

Or:

```json
{ "profile_id": 12, "job_description": "..." }
```

Provide **either** `profile_id` **or** `user_data`. Response `result`:

```json
{
  "score": 78,
  "grade": "B",
  "source": "structured",
  "categories": {
    "completeness": 90,
    "contact": 100,
    "content": 70,
    "ats_format": 80,
    "keyword_fit": 65
  },
  "checks": [
    {
      "id": "has_email",
      "category": "contact",
      "passed": true,
      "weight": 10,
      "message": "Valid email found.",
      "tip": null
    }
  ],
  "keywords": {
    "matched": ["flutter", "dart"],
    "missing": ["ci/cd"],
    "coverage_percent": 65
  }
}
```

`keywords` / `keyword_fit` are only present when `job_description` is sent. With a JD, overall score = `0.7 * structural + 0.3 * keyword_coverage`.

### PDF upload check

```http
POST /api/v1/cvs/ats-check/upload
Content-Type: multipart/form-data
```

Form fields: `file` (PDF), optional `job_description`, optional `language`.  
`result.source` is `pdf`. Scanned/image-only PDFs fail the parseability check.

---

## 7. Public Profiles

One public profile per authenticated user.

| Method | Endpoint | Auth | Notes |
|--------|----------|------|-------|
| `GET` | `/api/v1/public-profiles/templates` | — | Public. Active templates. |
| `GET` | `/api/v1/public-profiles` | Bearer | Current user's profile. |
| `POST` | `/api/v1/public-profiles` | Bearer | Create (409 if already exists). |
| `PUT` | `/api/v1/public-profiles` | Bearer | Update. |
| `DELETE` | `/api/v1/public-profiles` | Bearer | Delete. |

```json
POST /api/v1/public-profiles
{
  "slug": "john-doe",
  "language": "en",
  "is_public": true,
  "headline": "Flutter & Laravel developer",
  "about": "...",
  "public_profile_template_id": 1,
  "user_data": { "firstName": "John", "lastName": "Doe", "...": "..." }
}
```

---

## 8. Analytics

| Method | Endpoint | Auth | Notes |
|--------|----------|------|-------|
| `POST` | `/api/v1/analytics/click` | — | Landing click beacon. **204 No Content**. |

```json
{ "target": "app_store", "page": "/" }
```

`target` must be `app_store` or `play_store`. Designed for `navigator.sendBeacon()`.

---

## Error Responses

| Status | When |
|--------|------|
| `200` | Success. |
| `201` | Resource created. |
| `400` | Bad request / validation error. |
| `401` | Missing or invalid Bearer token. |
| `403` | Authenticated but not allowed (rare — most cases return `404`). |
| `404` | Resource not found, or not owned by the caller. |
| `422` | Validation failed (Laravel default). |
| `500` | Server error (e.g. PDF generation failure). |

```json
{
  "success": false,
  "message": "Human-readable error message",
  "code": 400,
  "errors": null
}
```

---

## Notes

### Authentication Behaviour

- **Unauthenticated requests:** can call `POST /cvs`, `POST /cvs/print`, `POST /cvs/ats-check`, `POST /cvs/ats-check/upload`, `POST /cover-letters`, `POST /cover-letters/print`, `GET /shares/templates`, `GET /cover-letters/templates`, `GET /public-profiles/templates`, `POST /analytics/click`, and `/cover-letter/{id}` (HTML preview).
- **Authenticated requests:** full CRUD on the caller's own CVs, cover letters, and public profile; PDFs and templates are still available.
- **Token storage** is handled by the collection's pre-request + test scripts — no manual copy/paste.

### PDF / Print Behaviour

- All `/print` endpoints (CV and Cover Letter) return **JSON** with `result.url`. Open the URL in a browser or `url_launcher` to download.
- The `last_pdf_url` variable is auto-set after any print request.

### Date Formats

- **Birthdate:** `YYYY-MM-DD` (e.g. `1990-05-15`).
- **Education / Experience / Project dates:** `YYYY-MM` (e.g. `2021-03`).
- Use `null` for ongoing / current items where `to` applies.

### Languages

- Supported: `en`, `ar`, `tr`. RTL for `ar`.
- Locale is taken from the CV / cover letter's `language` field for PDFs and the HTML preview.

### Suggested Frontend Flow (Cover Letter)

1. `GET /cover-letters/templates` → show picker (`preview` + `is_default`).
2. Form edits → keep local `user_data` + `language` + selected `template_id`.
3. Optional live preview after save: load `/cover-letter/{id}?template_id=...` in a WebView.
4. Save: `POST /cover-letters` (guest or auth) or `PUT /cover-letters/{id}` (auth).
5. Export: `POST /cover-letters/print` with `template_id` + `cover_letter_id` (or `user_data`) → open `result.url`.
6. Library screen (logged in): `GET /cover-letters` → show / edit / delete.

---

## Support

For issues or questions about the API, refer to the main project documentation or contact the development team.
