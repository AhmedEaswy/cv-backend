# Cover Letter API — Frontend Integration Guide

Base URL: `{{base_url}}/api/v1`  
Auth: Laravel Sanctum Bearer token (`Authorization: Bearer {token}`)  
Envelope:

```json
{
  "success": true,
  "message": "...",
  "result": {}
}
```

Errors:

```json
{
  "success": false,
  "message": "...",
  "code": 400,
  "errors": null
}
```

---

## What’s new / changed

| Change | Impact for frontend |
|--------|---------------------|
| Full Cover Letter CRUD + print + templates | Same flow as CVs, under `/cover-letters` |
| `GET /cover-letters/templates` → `preview` is a **full URL** | Use `preview` directly in `<img>` / Image widgets (no need to prefix storage path) |
| `POST /cover-letters/print` returns JSON `{ url }` | Open/download the PDF from `result.url` (not a raw PDF body) |
| HTML preview route | `GET /cover-letter/{id}?template_id=` for iframe/WebView preview |
| Seeded templates | `ats-classic` (default), `professional` |
| Languages | `en`, `ar`, `tr` — PDF/preview locale follows `language` |

---

## Endpoints overview

| Method | Path | Auth | Description |
|--------|------|------|-------------|
| `GET` | `/cover-letters/templates` | Public | List active cover letter templates |
| `POST` | `/cover-letters` | Public* | Create cover letter |
| `POST` | `/cover-letters/print` | Public* | Generate PDF → returns URL |
| `GET` | `/cover-letters` | Required | List current user’s cover letters |
| `GET` | `/cover-letters/{id}` | Required | Get one cover letter |
| `PUT` | `/cover-letters/{id}` | Required | Update cover letter |
| `DELETE` | `/cover-letters/{id}` | Required | Soft-delete cover letter |
| `GET` | `/cover-letter/{id}` | Web (no API prefix) | HTML template preview |

\*Public create/print still accept an authenticated user when a token is sent (`user_id` is taken from the token when present).

Query on list: `?language=en|ar|tr` filters by language.

---

## `user_data` shape

Used on create, update, and print (when not using `cover_letter_id`).

| Field | Type | Notes |
|-------|------|--------|
| `firstName` | string | Required on print when sending `user_data` |
| `lastName` | string | Required on print when sending `user_data` |
| `email` | string | |
| `phone` | string \| null | |
| `address` | string \| null | |
| `jobTitle` | string | Sender role / title |
| `companyName` | string | Sender company (optional) |
| `recipientName` | string | |
| `recipientTitle` | string \| null | |
| `recipientCompany` | string \| null | |
| `subject` | string | |
| `body` | string | Letter body (HTML/text as stored) |
| `closing` | string \| null | e.g. "Sincerely" |
| `experiences` | array | Optional; passed through as-is |

---

## Response object (`result` for a cover letter)

```json
{
  "id": 1,
  "user_id": 5,
  "name": "Application – Acme",
  "language": "en",
  "is_public": false,
  "sections_order": ["info", "body"],
  "cover_letter_template_id": 1,
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
    "body": "I am writing to apply...",
    "closing": "Sincerely",
    "experiences": []
  },
  "created_at": "2026-07-25T10:00:00+00:00",
  "updated_at": "2026-07-25T10:00:00+00:00"
}
```

---

## 1. List templates

`GET /api/v1/cover-letters/templates`

```json
{
  "success": true,
  "message": "...",
  "result": [
    {
      "id": 1,
      "name": "ats-classic",
      "preview": "http://localhost:8000/storage/cover-letter-templates/ats-classic.svg",
      "description": "ATS-friendly cover letter...",
      "is_default": true
    },
    {
      "id": 2,
      "name": "professional",
      "preview": "http://localhost:8000/storage/cover-letter-templates/professional.svg",
      "description": "Professional cover letter layout...",
      "is_default": false
    }
  ]
}
```

**Frontend notes**
- Prefer `is_default: true` when no template is selected yet.
- `preview` is already an absolute URL (recent fix). Treat `null` as “no image”.

---

## 2. Create

`POST /api/v1/cover-letters`

```json
{
  "name": "Application – Acme",
  "language": "en",
  "cover_letter_template_id": 1,
  "sections_order": ["info", "body"],
  "user_data": {
    "firstName": "John",
    "lastName": "Doe",
    "email": "john@example.com",
    "jobTitle": "Software Engineer",
    "recipientName": "Hiring Manager",
    "recipientCompany": "Acme Inc.",
    "subject": "Application for Software Engineer",
    "body": "I am writing to apply for the Software Engineer role...",
    "closing": "Sincerely"
  }
}
```

| Field | Required | Rules |
|-------|----------|--------|
| `name` | Yes | string, max 255 |
| `language` | No | `en` \| `ar` \| `tr` (default `en`) |
| `cover_letter_template_id` | No | must exist in `cover_letter_templates` |
| `sections_order` | No | string[] |
| `user_id` | No | only if creating without auth token |
| `user_data` | No | see table above |

Status: `201`

---

## 3. List / show / update / delete (auth required)

**List:** `GET /api/v1/cover-letters?language=en`  
**Show:** `GET /api/v1/cover-letters/{id}`  
**Update:** `PUT /api/v1/cover-letters/{id}` — all fields optional; same shape as create  
**Delete:** `DELETE /api/v1/cover-letters/{id}` — soft delete; `result` is `null`

Ownership: only the owner can show/update/delete. Others get `404`.

---

## 4. Print (PDF URL)

`POST /api/v1/cover-letters/print`

**Option A — existing cover letter**

```json
{
  "cover_letter_id": 1,
  "template_id": 1
}
```

**Option B — ad-hoc data (no saved letter yet)**

```json
{
  "template_id": 1,
  "name": "Draft Cover Letter",
  "language": "en",
  "user_data": {
    "firstName": "John",
    "lastName": "Doe",
    "email": "john@example.com",
    "body": "..."
  }
}
```

| Field | Required | Notes |
|-------|----------|--------|
| `template_id` | Yes | Active cover letter template ID |
| `cover_letter_id` | One of | Existing letter OR `user_data` |
| `user_data` | One of | Required if no `cover_letter_id`; needs `firstName` + `lastName` |
| `name` / `language` / `sections_order` | No | Used when creating from `user_data` |

**Success**

```json
{
  "success": true,
  "message": "...",
  "result": {
    "url": "http://localhost:8000/storage/cover-letters/cl_xxxx.pdf"
  }
}
```

Open `result.url` in browser / download manager / `url_launcher`.

If the letter belongs to another user → `404`. Inactive/missing template → `404`.

---

## 5. HTML preview (WebView / iframe)

Not under `/api` — useful for live preview before/after save:

```
GET {base_url}/cover-letter/{id}?template_id={optional}
```

- Uses saved `cover_letter_template_id` when `template_id` query is omitted.
- Falls back to default active template, then any active template.
- Locale follows the cover letter `language` (`en` / `ar` / `tr`).

Example:

```
http://localhost:8000/cover-letter/12?template_id=1
```

---

## Suggested frontend flow

1. `GET /cover-letters/templates` → show picker (`preview` + `is_default`).
2. Form edits → keep local `user_data` + `language` + selected `template_id`.
3. Optional live preview after save: load `/cover-letter/{id}?template_id=...` in WebView.
4. Save: `POST /cover-letters` (guest or auth) or `PUT /cover-letters/{id}` (auth).
5. Export: `POST /cover-letters/print` with `template_id` + `cover_letter_id` (or `user_data`) → open `result.url`.
6. Library screen (logged in): `GET /cover-letters` → show / edit / delete.

---

## Quick checklist for the front app

- [ ] Add Cover Letter feature module mirroring CV CRUD paths
- [ ] Template list uses full `preview` URLs
- [ ] Print handler expects JSON `{ url }`, not binary PDF
- [ ] Support `language`: `en` | `ar` | `tr` (RTL for `ar`)
- [ ] Map UI fields to `user_data` keys above (camelCase)
- [ ] Use `cover_letter_template_id` on save; `template_id` on print
- [ ] Optional: HTML preview via `/cover-letter/{id}`
