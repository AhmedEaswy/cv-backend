# Authentication (mobile)

## Overview

| Method | Endpoint | Mobile use |
|--------|----------|------------|
| Register | `POST /auth/register` | Email signup |
| Login | `POST /auth/login` | Email login |
| Me | `GET /auth/me` | Session hydrate |
| Logout | `POST /auth/logout` | Revoke current token |
| Forgot password | `POST /auth/forgot-password` | Send reset email |
| Verify reset token | `POST /auth/reset-token` | Optional pre-check |
| Reset password | `POST /auth/reset-password` | Set new password |
| Google (native) | `POST /auth/google` | **Preferred** Google Sign-In on mobile |
| OAuth redirect | `GET /auth/{provider}/redirect` | Browser / WebView flows |
| OAuth callback | `GET /auth/{provider}/callback` | Browser / WebView flows |

`provider` is `google` or `linkedin`.

Tokens do not auto-expire (`expires_at` is null in Sanctum config). Treat logout and secure storage carefully.

Inactive users (`active = false`) get **403** on login / Google exchange.

---

## Register

```http
POST /api/v1/auth/register
```

```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

- Password min length: **8**
- Response **201** with `result.user` + `result.token`

---

## Login

```http
POST /api/v1/auth/login
```

```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

- Invalid credentials → **401**
- Inactive account → **403**

---

## Current user

```http
GET /api/v1/auth/me
Authorization: Bearer {token}
```

`result.user` includes: `id`, `name`, `email`, `first_name`, `last_name`, `phone`, `active`, `created_at`.

Email verification is **not** required for API routes (web portal may still require it).

---

## Logout

```http
POST /api/v1/auth/logout
Authorization: Bearer {token}
```

Deletes the **current** personal access token only. Clear the token from device storage afterward.

---

## Password reset

1. `POST /auth/forgot-password` `{ "email": "…" }` — email must exist (`exists:users,email`)
2. User opens the reset link from email (web) or you deep-link with the token
3. Optional: `POST /auth/reset-token` `{ "email", "token" }` to validate
4. `POST /auth/reset-password`:

```json
{
  "email": "john@example.com",
  "token": "TOKEN_FROM_EMAIL",
  "password": "new-password",
  "password_confirmation": "new-password"
}
```

---

## Google Sign-In (native — recommended)

1. Sign in with Google on device (Google Sign-In SDK / Credential Manager / etc.)
2. Obtain the Google **access token** (not an ID token unless your backend is changed)
3. Exchange with Laravel:

```http
POST /api/v1/auth/google
Content-Type: application/json

{
  "code": "<google_access_token>"
}
```

**Quirk:** the JSON field is named `code`, but the server treats it as a Google **access token**, not an OAuth authorization code.

- Rate limit: **10 requests / minute**
- Success: same shape as login — `result.token` + `result.user`
- Creates the user if they do not exist yet (via Socialite userinfo)

### Browser OAuth (optional)

```http
GET /api/v1/auth/google/redirect   → { "result": { "url": "https://accounts.google.com/…" } }
GET /api/v1/auth/google/callback?code=…&state=…  → { "result": { "token", "user" } }
```

Same for `linkedin`. Prefer native Google + `POST /auth/google` on mobile.

---

## Token storage checklist

- Store Bearer token in OS secure storage
- Attach on every authenticated request
- On **401**, clear token and send user to login
- Do not put tokens in analytics / crash logs
- Agent tokens (`/agent-tokens`) are separate from the user session token — see [API reference](./api-reference.md#agent-tokens)

---

## Locale

Send `Accept-Language` to localize `message` strings. Supported locales include `en`, `ar`, `tr`, `es`, `fr`, `de`, `ur`.
