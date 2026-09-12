# Getting started (mobile)

## Base URL

All mobile API calls use:

```
{APP_URL}/api/v1
```

| Environment | Example `base_url` | Full API prefix |
|-------------|--------------------|-----------------|
| Laragon (`cv.test`) | `https://cv.test` | `https://cv.test/api/v1` |
| Artisan serve | `http://localhost:8000` | `http://localhost:8000/api/v1` |
| Production | your deployed host | `{host}/api/v1` |

Store only the host (or host + scheme) in the client config, then prefix `/api/v1` on every call.

## Required headers

```http
Accept: application/json
Content-Type: application/json
Authorization: Bearer {token}   # when authenticated
Accept-Language: en             # optional: en | ar | tr | es | fr | de | ur
```

For PDF ATS upload use `multipart/form-data` (no JSON `Content-Type`).

## Auth model (mobile)

Use **Sanctum Bearer tokens** only.

1. Register or login → read `result.token`
2. Persist securely (Keychain / Keystore / `flutter_secure_storage`)
3. Send `Authorization: Bearer {token}` on protected routes
4. Logout → `POST /auth/logout` (revokes **this** token only)

Do **not** rely on cookies, CSRF, or `SANCTUM_STATEFUL_DOMAINS` for native apps.

## First successful call

```http
POST /api/v1/auth/login
Content-Type: application/json
Accept: application/json

{
  "email": "user@example.com",
  "password": "your-password"
}
```

```json
{
  "success": true,
  "message": "...",
  "result": {
    "user": { "id": 1, "name": "Jane", "email": "user@example.com" },
    "token": "1|xxxxxxxx"
  }
}
```

Then:

```http
GET /api/v1/auth/me
Authorization: Bearer 1|xxxxxxxx
Accept: application/json
```

## Response envelope

Almost every endpoint returns:

```json
{
  "success": true,
  "message": "Human-readable status",
  "result": {}
}
```

Errors:

```json
{
  "success": false,
  "message": "…",
  "code": 422,
  "errors": { "email": ["The email has already been taken."] }
}
```

**Exception:** `POST /analytics/click` returns **204 No Content** (empty body).

## Public vs authenticated

| Access | Examples |
|--------|----------|
| Public | register, login, password reset, Google exchange, template lists, guest CV/cover-letter create & print, ATS check, analytics click |
| Optional Bearer | `POST /cvs`, `POST /cvs/print`, `POST /cover-letters`, `POST /cover-letters/print` — if a token is present, resources attach to that user |
| Required Bearer | logout, me, CV/CL list/show/update/delete/duplicate, public-profile CRUD, portal stats, AI settings, agent tokens |

## PDF behaviour (important)

`POST /cvs/print` and `POST /cover-letters/print` (and guest create-with-`template_id`) return **JSON**, not binary PDF:

```json
{ "success": true, "result": { "url": "https://…/storage/cvs/….pdf" } }
```

Open `result.url` in the system browser / download manager / `url_launcher`.

## CORS

CORS only affects browser clients. Native mobile HTTP clients ignore it.

## Postman

Import [`../CV_Mobile_API.postman_collection.json`](../CV_Mobile_API.postman_collection.json), set `base_url`, run Login.

## Next

- [Authentication](./authentication.md)
- [API reference](./api-reference.md)
- [Data models](./data-models.md)
- [Workflows](./workflows.md)
