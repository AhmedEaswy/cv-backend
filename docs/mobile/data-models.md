# Data models

## Envelope

```json
{
  "success": true,
  "message": "…",
  "result": {}
}
```

Error:

```json
{
  "success": false,
  "message": "…",
  "code": 422,
  "errors": { "field": ["…"] }
}
```

Pagination (templates when `?page=` is sent):

```json
{
  "result": {
    "data": [],
    "meta": {
      "current_page": 1,
      "last_page": 3,
      "per_page": 9,
      "total": 25,
      "has_more": true
    }
  }
}
```

Without `page`, template lists return a flat array in `result`.

---

## CV `user_data` (API format)

Use this shape on create / update / print / ATS. The server maps to internal Profile JSON columns via `CVDataMapper`.

| Field | Type | Notes |
|-------|------|-------|
| `firstName` | string | |
| `lastName` | string | |
| `jobTitle` | string | |
| `email` | string | |
| `address` | string | |
| `portfolioUrl` | string | |
| `phone` | string | |
| `summary` | string | |
| `birthdate` | string | `YYYY-MM-DD` |
| `photo` | string | Base64 / data-URI / URL / storage path. Images jpeg/png/webp, max ~2MB when uploaded as data |
| `skills` | `{ name }[]` | |
| `educations` | object[] | `institution`, `degree`, `fieldOfStudy`, `description?`, `from`, `to` (`YYYY-MM`) |
| `experiences` | object[] | `position`, `company`, `location?`, `description?`, `from`, `to`, `current` (bool) |
| `projects` | object[] | `title`, `description?`, `technologies?`, `url?`, `from`, `to`, `current?` |
| `languages` | object[] | `name`, `proficiencyLevel` **1–5** |
| `interests` | `{ name }[]` | |

### Proficiency levels

| API `proficiencyLevel` | Meaning |
|------------------------|---------|
| 1 | beginner |
| 2 | intermediate |
| 3 | advanced |
| 4 | fluent |
| 5 | native |

### Field mapping (API ↔ DB) — for debugging only

| API | Stored as |
|-----|-----------|
| `experiences[].company` | `experiences[].name` |
| `experiences[].current` | `experiences[].currentlyWorkingHere` |
| `projects[].title` | `projects[].name` |
| `languages[].name` | `languages[].language` |
| `languages[].proficiencyLevel` | `languages[].level` (string) |
| `interests[].name` | `interests[].interest` |
| personal + skills | `info` JSON |

Mobile clients should **only** send/read the API format. Responses already remap to API camelCase.

### Example CV profile `result`

```json
{
  "id": 12,
  "user_id": 3,
  "name": "Software Engineer CV",
  "language": "en",
  "template_id": 1,
  "is_public": false,
  "sections_order": ["Personal Information", "Experience", "Education", "Skills"],
  "user_data": {
    "firstName": "Jane",
    "lastName": "Doe",
    "jobTitle": "Backend Engineer",
    "email": "jane@example.com",
    "skills": [{ "name": "Laravel" }],
    "experiences": [
      {
        "position": "Engineer",
        "company": "Acme",
        "from": "2022-01",
        "to": null,
        "current": true
      }
    ],
    "languages": [{ "name": "English", "proficiencyLevel": 5 }]
  },
  "created_at": "…",
  "updated_at": "…"
}
```

List items may also include `latest_ats_score` and `latest_ats_grade`.

---

## Cover letter `user_data`

| Field | Type |
|-------|------|
| `firstName` | string |
| `lastName` | string |
| `email` | string |
| `phone` | string \| null |
| `address` | string \| null |
| `jobTitle` | string |
| `companyName` | string \| null |
| `recipientName` | string |
| `recipientTitle` | string \| null |
| `recipientCompany` | string \| null |
| `subject` | string |
| `body` | string |
| `closing` | string \| null |
| `experiences` | array (passthrough) |

Language for cover letters: **`en` \| `ar` \| `tr`** only.

---

## Public profile `user_data`

Richer than CV. Common fields:

`firstName`, `lastName`, `jobTitle`, `email`, `phone`, `address`, `city`, `country`, `photo`, `coverImage`, `website`, `birthdate`, `pronouns`, `socialLinks[]`, `experiences[]`, `educations[]`, `projects[]`, `skills[]`, `languages[]`, `services[]`, `testimonials[]`, `certifications[]`, `achievements[]`, `availability`, `cta`, `seo`

Top-level public profile fields (outside `user_data`): `slug`, `language`, `is_public`, `headline`, `about`, `sections_order`, `public_profile_template_id`.

---

## Dates

| Context | Format |
|---------|--------|
| Birthdate | `YYYY-MM-DD` |
| Education / experience / project ranges | `YYYY-MM` |
| Ongoing role | `to: null` + `current: true` |

---

## Languages

| Surface | Allowed |
|---------|---------|
| CV `language` | `en, ar, tr, es, fr, de, ur` |
| Cover letter / public profile / ATS `language` | usually `en, ar, tr` |
| `Accept-Language` header | same broader set for messages |

Arabic (`ar`) is RTL in PDF/HTML templates.

---

## Soft deletes

CVs, cover letters, public profiles, users, and CV templates use soft deletes. Deleted IDs are not returned in normal list/show endpoints.
