# API reference (`/api/v1`)

Paths below are relative to `{base_url}/api/v1`.

Auth column: **—** public · **Optional** Bearer attaches ownership when present · **Bearer** required.

---

## Auth

| Method | Path | Auth | Notes |
|--------|------|------|-------|
| POST | `/auth/register` | — | `name`, `email`, `password`, `password_confirmation` → `user` + `token` (201) |
| POST | `/auth/login` | — | `email`, `password` → `user` + `token` |
| POST | `/auth/logout` | Bearer | Revokes current token |
| GET | `/auth/me` | Bearer | Current user profile |
| POST | `/auth/forgot-password` | — | `email` (must exist) |
| POST | `/auth/reset-token` | — | `email`, `token` |
| POST | `/auth/reset-password` | — | `email`, `token`, `password`, `password_confirmation` |
| POST | `/auth/google` | — | `{ "code": "<google_access_token>" }` · throttle 10/min |
| GET | `/auth/{provider}/redirect` | — | `provider`: `google` \| `linkedin` → `{ url }` |
| GET | `/auth/{provider}/callback` | — | Query from provider → `token` + `user` |

Details: [authentication.md](./authentication.md)

---

## CVs (profiles)

DB model is `Profile`. Soft-deleted. Ownership failures usually return **404**.

| Method | Path | Auth | Notes |
|--------|------|------|-------|
| GET | `/cvs` | Bearer | Optional `?language=` · items include `latest_ats_score`, `latest_ats_grade` |
| GET | `/cvs/{id}` | Bearer | Owned CV only |
| POST | `/cvs` | Optional | Create. Guest + `template_id` → `{ url }` PDF instead of profile payload |
| PUT | `/cvs/{id}` | Bearer | Partial update; may include `is_public` |
| DELETE | `/cvs/{id}` | Bearer | Soft delete |
| POST | `/cvs/{id}/duplicate` | Bearer | Copy → new profile (201) |
| POST | `/cvs/print` | Optional | `template_id`* + (`profile_id` **or** `user_data`) → `{ url }` |

### Create / update body (top-level)

| Field | Required on create | Notes |
|-------|--------------------|-------|
| `name` | Yes | CV title |
| `language` | No | `en,ar,tr,es,fr,de,ur` (default `en`) |
| `sections_order` | No | string[] |
| `template_id` | No | Used for guest PDF path / association |
| `user_id` | No | Prefer Bearer instead |
| `user_data` | No | See [data-models.md](./data-models.md) |
| `is_public` | Update only | boolean |

### Print body

```json
{ "template_id": 1, "profile_id": 12 }
```

or ad-hoc:

```json
{
  "template_id": 1,
  "name": "Draft",
  "language": "en",
  "user_data": { "firstName": "Jane", "lastName": "Doe", "email": "jane@example.com" }
}
```

With ad-hoc `user_data`, `firstName` + `lastName` are required.

---

## Cover letters

| Method | Path | Auth | Notes |
|--------|------|------|-------|
| GET | `/cover-letters/templates` | — | Optional `?page=&per_page=` (default per_page 9, max 50) |
| GET | `/cover-letters` | Bearer | Optional `?language=` (`en`\|`ar`\|`tr`) |
| GET | `/cover-letters/{id}` | Bearer | |
| POST | `/cover-letters` | Optional | 201 |
| PUT | `/cover-letters/{id}` | Bearer | |
| DELETE | `/cover-letters/{id}` | Bearer | Soft delete |
| POST | `/cover-letters/print` | Optional | `template_id`* + (`cover_letter_id` **or** `user_data`) → `{ url }` |

### Create body

| Field | Required | Notes |
|-------|----------|-------|
| `name` | Yes | |
| `language` | No | `en` \| `ar` \| `tr` |
| `cover_letter_template_id` | No | Must exist |
| `sections_order` | No | string[] |
| `user_data` | No | See data models |

### HTML preview (web, not under `/api`)

```http
GET /cover-letter/{id}?template_id={optional}
```

Useful in a WebView for live preview.

---

## Templates (CV)

| Method | Path | Auth | Notes |
|--------|------|------|-------|
| GET | `/shares/templates` | — | Active CV templates; optional pagination via `?page=` |

Item fields include: `id`, `name`, `preview`, `description`, `supports_image`, `is_default`, timestamps.

Use `id` as `template_id` for print / guest create.

---

## ATS checker

Rule-based (no LLM). Public.

| Method | Path | Auth | Notes |
|--------|------|------|-------|
| POST | `/cvs/ats-check` | — | JSON: `profile_id` **or** `user_data`; optional `job_description`, `language` |
| POST | `/cvs/ats-check/upload` | — | Multipart: `file` (PDF ≤ 5MB), optional `job_description`, `language` |

### Result shape

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
      "label": "…",
      "message": "…",
      "tip": null
    }
  ],
  "keywords": {
    "matched": ["laravel"],
    "missing": ["docker"],
    "coverage_percent": 50
  },
  "check_id": 123
}
```

`keywords` / `keyword_fit` appear when `job_description` is provided. Upload sets `source` to `pdf`.

---

## Public profiles

One profile per authenticated user. Soft-deleted.

| Method | Path | Auth | Notes |
|--------|------|------|-------|
| GET | `/public-profiles/templates` | — | Flat list |
| GET | `/public-profiles` | Bearer | Current user's profile (404 if none) |
| POST | `/public-profiles` | Bearer | 201 · **409** if already exists |
| PUT | `/public-profiles` | Bearer | |
| DELETE | `/public-profiles` | Bearer | Soft delete |

### Create / update fields

`slug`, `language` (`en`\|`ar`\|`tr`), `is_public`, `headline`, `about`, `sections_order`, `public_profile_template_id`, `user_data`

Response extras: `public_url` (e.g. `https://host/u/{slug}`), `enable_contact_form`, `contact_form_recipient`.

Contact form submissions go to **web** `POST /u/{slug}/contact` (not under `/api/v1`). Inbox mark-read is portal-web only today.

---

## Portal

| Method | Path | Auth | Notes |
|--------|------|------|-------|
| GET | `/portal/stats` | Bearer | Dashboard aggregate |

Typical `result` keys: `cvs_count`, `cover_letters_count`, `top_ats_score`, `views_count`, `unread_messages`, `has_public_profile`, `public_profile_is_published`, `public_profile_slug`, `public_profile_url`, `latest_cv`, `latest_cover_letter`.

---

## AI settings

| Method | Path | Auth | Notes |
|--------|------|------|-------|
| GET | `/ai-settings` | Bearer | Config + provider catalog; never returns raw key |
| PUT | `/ai-settings` | Bearer | Upsert |

```json
{
  "provider": "openai",
  "model": "gpt-4o-mini",
  "api_key": "sk-…",
  "custom_url": null,
  "clear_api_key": false
}
```

`provider`: `openai` \| `openrouter` \| `custom`.

Response includes `has_api_key`, `providers`, `provider_models`, `base_url`, etc.

---

## Agent tokens

Optional. Used for MCP / agent tooling. Abilities are enforced on MCP tools, not on normal REST CRUD.

| Method | Path | Auth | Notes |
|--------|------|------|-------|
| GET | `/agent-tokens` | Bearer | List metadata (no plaintext) |
| POST | `/agent-tokens` | Bearer | Returns plaintext `token` **once** |
| DELETE | `/agent-tokens/{id}` | Bearer | Revoke |

Abilities: `cv:read`, `cv:write`, `ats:check`, `cover-letter:read`, `cover-letter:write`, `profile:read`, `profile:write`.

---

## Analytics

| Method | Path | Auth | Notes |
|--------|------|------|-------|
| POST | `/analytics/click` | — | **204** empty body |

```json
{ "target": "app_store", "page": "/" }
```

`target`: lowercase snake (`^[a-z][a-z0-9_]*$`), max 80 chars. Typical values: `app_store`, `play_store`.

---

## HTTP status cheat sheet

| Code | Meaning |
|------|---------|
| 200 | OK |
| 201 | Created |
| 204 | No content (analytics click) |
| 401 | Missing/invalid Bearer |
| 403 | Inactive account / forbidden |
| 404 | Missing or not owned |
| 409 | Public profile already exists |
| 422 | Validation failed |
| 429 | Throttled (`POST /auth/google`) |
| 500 | Server / PDF failure |

---

## Rate limits

Most `/api/v1` routes have **no** dedicated throttle in `routes/api.php`.

Notable: `POST /auth/google` → `throttle:10,1`.
