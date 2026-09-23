# Authentication (mobile)

## Overview

| Method | Endpoint | Mobile use |
|--------|----------|------------|
| Register | `POST /auth/register` | Email signup (sends OTP; no token yet) |
| Verify email | `POST /auth/verify-email` | Confirm register OTP → token |
| Resend verification | `POST /auth/resend-verification` | Resend register OTP |
| Login | `POST /auth/login` | Email login (unverified → 403) |
| Me | `GET /auth/me` | Session hydrate |
| Logout | `POST /auth/logout` | Revoke current token |
| Forgot password | `POST /auth/forgot-password` | Send reset OTP email |
| Reset password | `POST /auth/reset-password` | Set new password with OTP |
| Google (native) | `POST /auth/google` | **Preferred** Google Sign-In on mobile |
| LinkedIn (native) | `POST /auth/linkedin` | LinkedIn OpenID token; may create a CV |
| Apple (native) | `POST /auth/apple` | Sign in with Apple identity token |
| OAuth redirect | `GET /auth/{provider}/redirect` | Browser / WebView flows |
| OAuth callback | `GET /auth/{provider}/callback` | Browser / WebView flows |

`provider` is `google`, `linkedin`, or `apple`.

Tokens do not auto-expire (`expires_at` is null in Sanctum config). Treat logout and secure storage carefully.

Inactive users (`active = false`) get **403** on login / Google / LinkedIn / Apple exchange.
Unverified email/password users get **403** on login with `result.verification_required: true`.

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
- Creates the user with `email_verified_at = null`
- Emails a **6-digit OTP** via SMTP
- Response **201** with `result.verification_required: true` and `result.email` — **no token**

Then confirm:

```http
POST /api/v1/auth/verify-email
```

```json
{
  "email": "john@example.com",
  "code": "123456"
}
```

- Response **200** with `result.user` + `result.token`

Resend (throttled ~60s):

```http
POST /api/v1/auth/resend-verification
```

```json
{ "email": "john@example.com" }
```

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
- Unverified email → **403** with `result.verification_required: true` and `result.email` (a fresh OTP may be sent)

---

## Current user

```http
GET /api/v1/auth/me
Authorization: Bearer {token}
```

`result.user` includes: `id`, `name`, `email`, `first_name`, `last_name`, `phone`, `active`, `email_verified_at`, `created_at`.

Email verification is required before login returns a token for email/password accounts. Social sign-in marks email verified automatically.

---

## Logout

```http
POST /api/v1/auth/logout
Authorization: Bearer {token}
```

Deletes the **current** personal access token only. Clear the token from device storage afterward.

---

## Password reset

1. `POST /auth/forgot-password` `{ "email": "…" }` — always returns a generic success; emails a 6-digit OTP when the account exists
2. User enters the code in the app (or on the web reset page)
3. `POST /auth/reset-password`:

```json
{
  "email": "john@example.com",
  "code": "123456",
  "password": "new-password",
  "password_confirmation": "new-password"
}
```

OTP codes expire in **10 minutes** and allow at most **5** wrong attempts.

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

Same for `linkedin` (`POST /auth/linkedin` with the LinkedIn access token). Optional `import_cv` (boolean) creates a CV from LinkedIn profile data. Sign In with LinkedIn (OpenID) always includes name, email, and photo; experience/education/skills require LinkedIn Member Data Portability approval (`LINKEDIN_DMA_ENABLED`).

Feature flags: set `LINKEDIN_AUTH_ENABLED=false` (Laravel) and `NUXT_PUBLIC_LINKEDIN_AUTH_ENABLED=false` (Nuxt) to disable LinkedIn auth/import. Same pattern for Apple with `APPLE_AUTH_ENABLED` / `NUXT_PUBLIC_APPLE_AUTH_ENABLED`.

After login, `POST /cvs/import/linkedin` creates another CV from the stored token (or a fresh `code` access token).

### Apple Sign-In (native — recommended)

1. Sign in with Apple on device (AuthenticationServices / Credential Manager)
2. Obtain the Apple **identity token** (JWT)
3. Exchange with Laravel:

```http
POST /api/v1/auth/apple
Content-Type: application/json

{
  "code": "<apple_identity_token>",
  "nonce": "<optional_authorization_nonce>",
  "first_name": "Ada",
  "last_name": "Lovelace"
}
```

**Quirk:** the JSON field is named `code`, but the server treats it as an Apple **identity token**, not an OAuth authorization code.

- Optional `first_name` / `last_name` / `name` — Apple only returns the name on the **first** authorization; send them when the SDK provides them
- Optional `nonce` — verified against the identity token when present
- Configure `APPLE_CLIENT_ID` (Services ID for web) and `APPLE_NATIVE_CLIENT_ID` (Bundle ID) when they differ; native token `aud` must match
- Rate limit: **10 requests / minute**
- Success: same shape as login — `result.token` + `result.user`

Browser OAuth for Apple uses the web Services ID via `GET /api/v1/auth/apple/redirect` (or the Nuxt hop to `/auth/apple/redirect`).

Prefer native Google / Apple + `POST /auth/google` / `POST /auth/apple` on mobile.

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
