# Mobile app — changes to apply (handoff)

**Date:** 2026-09-30
**Backend:** `cv-backend` (Laravel API `/api/v1` + Nuxt web portal)
**Mobile repo audited:** `cv-mobile` v`1.3.0+16`

This document lists what exists on the web/backend today but is **missing in the mobile app**, and what to build. Exact request/response contracts live in the existing docs — this file tells you *what* to build and links to *how*:

- [Authentication](./authentication.md) · [API reference](./api-reference.md) · [Data models](./data-models.md) · [Workflows](./workflows.md) · [UI / UX guide](../ui-ux-guide.md)
- Postman: [`../CV_Mobile_API.postman_collection.json`](../CV_Mobile_API.postman_collection.json)

---

## 0. Where the mobile app is today

The app is **guest-only**:

| Area | Mobile today |
|------|--------------|
| Auth | No UI. `auth_token_store.dart` + Bearer interceptor exist but nothing ever sets a token. No `google_sign_in` / `sign_in_with_apple` packages. |
| CVs | Stored locally only (`GetStorage` key `cv_collection_f`). Server copy is created implicitly through `POST /cvs/print` (`profile_id` saved as `serverId`). Never listed from server. |
| Cover letters | Local + mirrored to server via `POST/PUT /cover-letters`. Delete on server only runs when a token exists (never today). |
| Dashboard / stats | None. Tabs: My CVs · Templates · Tips · Profile (local settings). |
| Public profile | None. |
| Inbox | None. |
| Notifications / push | Firebase Messaging subscribes to topics only; FCM token is **never sent** to the backend. |
| ATS | Implemented (`/cvs/ats-check` + `/cvs/ats-check/upload`). Does not send `profile_id`; `categories` not displayed. |
| Locales | `en`, `ar`, `tr` only. Backend supports `en, ar, tr, es, fr, de, ur`. |

Endpoints mobile already calls: `GET /shares/templates`, `POST /cvs/print`, `POST /cvs/ats-check`, `POST /cvs/ats-check/upload`, `GET /cover-letters/templates`, `POST/PUT/DELETE /cover-letters(/{id})`, `POST /cover-letters/print`, `POST /analytics/click`.

---

## 1. Global / networking changes

1. **Secure token storage** — move `auth_token` from `GetStorage` to `flutter_secure_storage` (Keychain / Keystore).
2. **401 handling** — on any 401 clear the token and route to login (the web does the same).
3. **Keep sending** `X-Anonymous-Id` (UUID), `X-App-Platform` (`ios`/`android`), `X-App-Version`, `X-OS-Version`, `X-Device-Model`, `Accept-Language`. Optionally add `X-Device-Type` (`Mobile`/`Tablet`) and send the real device model instead of the literal `iPhone`/`Android`.
4. **Two error shapes** — handle both:
   - Wrapped: `{ "success": false, "message", "code", "errors": {field: [msg]} }`
   - Plain Laravel (social auth, inbox reply, settings, devices…): `{ "message", "errors": {…} }` (no `success`/`code`).
   - Login-unverified 403 also carries `result: { verification_required: true, email }`.
5. **Pagination** — template lists return a flat array when `page` is omitted; with `?page=&per_page=` they return `{ data, meta: { current_page, last_page, per_page, total, has_more } }`. Inbox and notifications support the same.
6. **Locales** — add `es`, `fr`, `de`, `ur` (ARB files) to match the web. `ur` and `ar` are RTL. Note: cover letter `language` only accepts `en, ar, tr` on the backend.

---

## 2. User auth (email/password + OTP)

Build these screens (web equivalents: `frontend/app/pages/auth/*`):

| Screen | Endpoint | Notes |
|--------|----------|-------|
| Register | `POST /auth/register` | `name, email, password, password_confirmation` (min 8). Returns **201 without a token** → go to Verify. |
| Verify email (OTP) | `POST /auth/verify-email` | `email, code` (6 digits) → `result.token` + `result.user`. |
| Resend code | `POST /auth/resend-verification` | 1 per 60 s per email; 429 carries seconds in `errors.email`. Show countdown. |
| Login | `POST /auth/login` | 401 wrong credentials, 403 inactive, **403 + `result.verification_required`** → go to Verify with the email (a fresh OTP is sent). |
| Forgot password | `POST /auth/forgot-password` | Always generic success. |
| Reset password (OTP) | `POST /auth/reset-password` | `email, code, password, password_confirmation`. |
| Session hydrate | `GET /auth/me` | On app start if a token exists. Full user: `id, name, email, first_name, last_name, phone, active, email_verified_at, …`. Login/verify/social return only `{id, name, email}` — call `/auth/me` after. |
| Logout | `POST /auth/logout` | Revokes current token only. Also unregister the push token (section 6). |

OTP rules: 6 digits, expires in **10 min**, max **5** wrong attempts (5th wrong attempt invalidates the code), single use.

Details: [authentication.md](./authentication.md).

---

## 3. Google auth (and Apple / LinkedIn)

### Google — required
- Add `google_sign_in`.
- **Send the Google ACCESS token, not the ID token:**
  ```http
  POST /api/v1/auth/google
  { "code": "<google_access_token>" }
  ```
  The field is named `code` but the server calls Google userinfo with it as an access token. An `id_token` or server auth code will fail with 401.
  With `google_sign_in` v7+ the access token comes from the authorization client (`authorizationClient.authorizeScopes(['email','profile'])` → `accessToken`); on older versions it's `GoogleSignInAuthentication.accessToken`.
- Response: `result.token` + `result.user`. Throttle 10/min.
- Accounts are **auto-linked by email**: if a user registered with email/password using the same address, Google signs into that same account.
- Backend config only has the web `GOOGLE_CLIENT_ID`; the mobile iOS/Android OAuth clients just need to be in the same Google Cloud project.

### Apple — strongly recommended for iOS
App Store guideline 4.8: if the iOS app offers Google sign-in it must also offer Sign in with Apple.
```http
POST /api/v1/auth/apple
{ "code": "<apple_identity_token>", "nonce": "<raw nonce, optional>", "first_name": "…", "last_name": "…" }
```
Send the name on the **first** authorization only (Apple won't send it again). Backend `APPLE_NATIVE_CLIENT_ID` must equal the iOS bundle ID. Can be disabled server-side (`APPLE_AUTH_ENABLED=false` → 403).

### LinkedIn — optional
`POST /auth/linkedin { "code": "<linkedin_access_token>", "import_cv": true }` — can create a CV from the LinkedIn profile (`result.cv`). `POST /cvs/import/linkedin` imports another CV later. Can be disabled (`LINKEDIN_AUTH_ENABLED=false`).

---

## 4. Guest → account transition (important)

The backend does **not** merge guest data into the account after login. Guest CVs/cover letters keep `user_id = null` and will not appear in `GET /cvs` / `GET /cover-letters`.

Until a backend "claim" endpoint exists (see section 10), recommended mobile flow after login/register:
1. For each local CV: `POST /cvs` (with Bearer) with `name, language, template_id, sections_order, user_data, client_ref` → save the returned `id` as the new `serverId`.
2. Same for local cover letters with `POST /cover-letters`.
3. Then treat the server as source of truth (section 5).

---

## 5. Dashboard (user area)

Web: `/portal` (`frontend/app/pages/portal/index.vue`). Add a **Dashboard** tab/screen for signed-in users.

- `GET /portal/stats` →
  `cvs_count, cover_letters_count, top_ats_score, views_count, unread_messages, unread_notifications, has_public_profile, public_profile_is_published, public_profile_slug, public_profile_url, inbox_enabled, latest_cv {id,name,updated_at}, latest_cover_letter {id,name,updated_at}`
- UI on web:
  - Greeting with first name.
  - Stat cards: CVs · top ATS score · profile views · unread inbox · public profile status (none / draft / published).
  - **Recent activity** (latest 8, mixed CVs, cover letters, inbox messages) with filter pills All / CVs / Letters / Inbox.
  - **Create new** menu: New CV · Import from LinkedIn · New cover letter · Public profile.
  - Public profile card with its URL (copy/share) and quick actions.
- Data used: `GET /cvs`, `GET /cover-letters`, `GET /public-profiles`, `GET /public-profiles/inbox`, `GET /portal/stats`.

### 5.1 CVs (server-synced)
Web: `/portal/cvs`, `/portal/cvs/[id]/edit`.

| Action | Endpoint |
|--------|----------|
| List | `GET /cvs` (flat array, optional `?language=`). Each item includes `latest_ats_score`, `latest_ats_grade` — show as a badge. |
| Open | `GET /cvs/{id}` |
| Create blank | `POST /cvs` (signed-in, empty `user_data` → server pre-fills name/email) |
| Save / autosave | `PUT /cvs/{id}` — partial; `user_data` personal info is **merged**, any list you send (skills, experiences…) **replaces** the stored list. Web autosaves every ~900 ms after edits. |
| Duplicate | `POST /cvs/{id}/duplicate` → 201, name gets "(Copy)" suffix |
| Delete | `DELETE /cvs/{id}` |
| PDF | `POST /cvs/print { template_id, profile_id }` → `{ url, profile_id }` (fields sent alongside are saved first) |
| Photo | `user_data.photo` accepts base64 / data URI (JPEG/PNG/WebP, max 2 MB); response returns an absolute URL |
| Live HTML preview | `GET /profile/{id}?template_id=` (web route, WebView) |
| Import LinkedIn | `POST /cvs/import/linkedin` |

**Missing in mobile:** server list, duplicate, server delete, photo upload, ATS badge per CV, template switch on an existing CV.

### 5.2 Cover letters
Web: `/portal/cover-letters`.

Same pattern: `GET /cover-letters`, `GET /cover-letters/{id}`, `POST`, `PUT /{id}`, `DELETE /{id}`, `POST /cover-letters/print`, and **new** `POST /cover-letters/{id}/duplicate` (201, returns the copy).

> ⚠️ The cover-letter duplicate endpoint is new and **not deployed yet** — wait for the backend release before shipping it.

**Missing in mobile:** list from server, duplicate, delete on server for signed-in users, `language` limited to `en/ar/tr`.

### 5.3 Public profile (one per user)
Web: `/portal/public-profile`. Not linked to a CV — it's its own record with its own data.

| Action | Endpoint |
|--------|----------|
| Get | `GET /public-profiles` (404 = no profile yet → show "Create") |
| Create | `POST /public-profiles` (409 if one exists) |
| Update | `PUT /public-profiles` (partial) |
| Delete | `DELETE /public-profiles` |
| Templates | `GET /public-profiles/templates` |
| Verify custom domain | `POST /public-profiles/verify-custom-domain-dns` |

Editor sections to build: publish toggle (`is_public`), slug (lowercase `a-z0-9-`, reserved words rejected), headline, about, photo / cover image URLs, contact details, **social links** (`platform` from a fixed list, `custom` needs `label`), experiences, educations, projects, skills, languages, services, testimonials, certifications, achievements, availability, CTA, SEO (`meta_title` ≤120, `meta_description` ≤320, `og_image`, `robots`), template, sections order, `enable_inbox` / `enable_contact_form`, URL mode (`slug` | `subdomain` | `custom_domain` + DNS TXT instructions from `custom_domain_dns_host` / `custom_domain_dns_value`).

Show and share `public_url` (best available URL; `/u/{slug}` fallback). Views are counted into `views_count` (dashboard stat).

### 5.4 Inbox management
Web: `/portal/inbox`. Visitors send messages through the public profile contact form; the owner manages them here. Only show when `stats.inbox_enabled` is true.

| Action | Endpoint |
|--------|----------|
| List | `GET /public-profiles/inbox` (returns `[]` if no profile / inbox disabled; `?page=&per_page=` supported) |
| Mark read | `POST /public-profiles/inbox/{id}/read` (web marks read when a message is opened) |
| Reply | `POST /public-profiles/inbox/{id}/reply { body }` (1–10000 chars) → 201 reply with `delivery_status: "pending"` |
| Retry failed reply | `POST /public-profiles/inbox/{id}/replies/{replyId}/retry` (only when `failed`) |
| Report spam | `POST /public-profiles/inbox/{id}/spam` (hides it + blocks sender; confirm dialog first) |

- Message: `id, name, email, subject, message, is_read, is_spam, created_at, read_at, replies[]`.
- Reply: `id, body, from_email, from_name, mail_mode, delivery_status (pending → queued → sent | failed), error_message, sent_at, failed_at, created_at`. Status changes asynchronously — re-fetch (pull-to-refresh) to update. Show a status chip + Retry button on `failed`.
- Filters All / Unread / Read are client-side.
- Web shows a banner linking to "Sending email" settings (custom SMTP) — optional on mobile.

### 5.5 ATS
Already implemented. Improvements to match web (`components/portal/AtsModal.vue`):
- For a server CV, send `profile_id` instead of `user_data` so the result is saved and appears as `latest_ats_score` in the CV list / `top_ats_score` in stats.
- Show `categories` scores (`completeness, contact, content, ats_format, keyword_fit`).
- `language` for ATS accepts `en, ar, tr` only.

---

## 6. Push notifications (Firebase Cloud Messaging) & notification center

### 6.1 What triggers a notification

When a visitor sends a message through the user's public profile contact form, the backend (`ContactMessageController`) does three things:

| Channel | Condition | What happens |
|---------|-----------|--------------|
| In-app notification | Always | A row is stored in Laravel's `notifications` table → appears in `GET /notifications` |
| Email | User setting `notify_contact_email = true` | Email sent to the profile's contact recipient |
| **FCM push** | User setting `notify_contact_push = true` | Queued job `SendContactPushNotificationJob` sends a push to **every device token** the user registered |

`contact_message` is currently the only notification type. Build the client generically (switch on `type`) so new types can be added later.

### 6.2 Firebase setup (mobile side)

The app already uses `firebase_messaging` (subscribes to `app_promotion` / `TPITO` topics) — keep that. What's missing is **user-targeted** push:

1. **Same Firebase project** — the backend sends via FCM HTTP v1 using a service account (`FIREBASE_CREDENTIALS` env on the server). That service account must belong to the **same Firebase project** as the mobile app's `google-services.json` / `GoogleService-Info.plist`. Coordinate with the backend to confirm the project ID.
2. **iOS** — APNs auth key uploaded in Firebase console, *Push Notifications* + *Background Modes → Remote notifications* capabilities enabled, request permission before reading the token.
3. **Android 13+** — request `POST_NOTIFICATIONS` runtime permission.
4. **Foreground display** — FCM does not show a banner while the app is in the foreground; use `flutter_local_notifications` (or `setForegroundNotificationPresentationOptions` on iOS) to show it.

### 6.3 Register / unregister the device token

Requires Bearer token.

```http
POST /api/v1/devices/push-token
Authorization: Bearer {token}

{ "token": "<fcm_token>", "platform": "ios", "app_version": "1.4.0+17" }
```

- `token` required (max 512), `platform` = `ios` | `android`, `app_version` optional (max 40).
- **Upsert by token** → 201 `{ id, platform }`. If the same device logs in with another account, the token moves to the new user.

```http
DELETE /api/v1/devices/push-token
Authorization: Bearer {token}

{ "token": "<fcm_token>" }
```

When to call:

| Moment | Action |
|--------|--------|
| After login / register / social sign-in | `FirebaseMessaging.instance.getToken()` → `POST /devices/push-token` |
| App start with a saved session | Re-send (cheap upsert, keeps `last_used_at` fresh) |
| `FirebaseMessaging.instance.onTokenRefresh` | `POST` the new token |
| Logout | `DELETE /devices/push-token` **before** `POST /auth/logout` (needs the Bearer token) |

Tokens that FCM reports as invalid (`UNREGISTERED`, `INVALID_ARGUMENT`, 404) are deleted automatically on the server.

### 6.4 Push payload

```json
{
  "notification": {
    "title": "<APP_NAME>",
    "body": "<subject or 'Message from {name}'>: <first 120 chars of the message>"
  },
  "data": {
    "type": "contact_message",
    "message_id": "123",
    "deep_link": "cv://inbox/123",
    "profile_slug": "john-doe"
  }
}
```

All `data` values are strings. Handle taps in all three states:

- **Foreground:** `FirebaseMessaging.onMessage` → show local notification + refresh unread badges.
- **Background:** `FirebaseMessaging.onMessageOpenedApp`.
- **Terminated:** `FirebaseMessaging.instance.getInitialMessage()` on startup.

On tap: open Inbox and select `message_id` (or parse `deep_link` `cv://inbox/{id}`). Register the `cv://` scheme in iOS/Android and in `go_router` so the deep link also works from outside FCM. If the user is logged out, send them to login first, then continue to the message.

### 6.5 Notification center (in-app)

Web: bell icon with unread badge in the portal header (`frontend/app/components/portal/PortalNotificationBell.vue`) — shows the latest 10, "Mark all as read", and "View inbox".

| Action | Endpoint |
|--------|----------|
| List | `GET /notifications` → `{ data: [...], unread_count }` (latest 50). With `?page=&per_page=` (default 20, max 50) → `{ data, meta, unread_count }` |
| Mark one read | `POST /notifications/{id}/read` → returns the updated item (404 if not found) |
| Mark all read | `POST /notifications/read-all` |

Item:

```json
{
  "id": "9f1c…-uuid",
  "type": "contact_message",
  "title": "New message from Sara",
  "data": {
    "type": "contact_message",
    "message_id": 123,
    "name": "Sara",
    "subject": "Job opportunity",
    "preview": "Hi, I saw your profile…",
    "profile_slug": "john-doe"
  },
  "read_at": null,
  "created_at": "2026-09-30T08:10:00+00:00"
}
```

- `id` is a **UUID string**, not an integer.
- `title` is already localized by the server (send `Accept-Language`).
- Unread = `read_at == null`. Badge count: `unread_count` here, or `unread_notifications` from `GET /portal/stats`.
- Tapping a `contact_message` item → mark it read → open Inbox on `data.message_id`.
- Suggested UI: bell icon in the app bar with badge → notifications list (pull-to-refresh, infinite scroll with `page`, "Mark all as read").
- Refresh the list and badges when a push arrives in the foreground and when the app resumes.

Note: reading the notification and reading the inbox message are separate. Opening the message should also call `POST /public-profiles/inbox/{id}/read`.

### 6.6 Notification settings

Web: `/portal/settings/notifications`.

```http
GET /api/v1/settings/notifications
PUT /api/v1/settings/notifications
{ "notify_contact_email": true, "notify_contact_push": true }
```

Both default to `true`. Show two toggles: "Email me when someone contacts me" and "Push notification when someone contacts me". Turning push off stops server sends; you don't need to unregister the token.

---

## 7. Items you didn't list but are missing in mobile

These exist on the web/backend and should be planned too:

1. **Push notifications (FCM), notification center, notification settings** — see section 6.
2. **Sign in with Apple** — required on iOS if Google sign-in ships (section 3).
3. **Guest data migration after login** — section 4.
4. **Extra locales** `es, fr, de, ur` — section 1.
5. **Templates gallery for public profiles** — `GET /public-profiles/templates`.
6. **LinkedIn CV import** — section 3.
7. **Account settings (profile edit, change password, delete account)** — ⚠️ **no API exists yet** (see section 10). Delete account is **mandatory** for App Store (guideline 5.1.1(v)) once sign-up ships.
8. **Optional / web-only for now:** sending-email (custom SMTP) settings `/settings/outbound-mail*`, AI settings `/ai-settings`, agent/MCP tokens `/agent-tokens`. Low priority on mobile.

---

## 8. Suggested mobile navigation

- **Guest:** current tabs (My CVs · Templates · Tips · Profile) + "Sign in to sync" CTA.
- **Signed in:** Dashboard · CVs & Letters · Public profile · Inbox (badge = `unread_messages`) · Profile/Settings (account, notifications, language, logout).
- **App bar:** notification bell with `unread_count` badge → notification center (section 6.5).

---

## 9. Suggested order of work

1. Secure token storage, 401 handling, error parsing, `/auth/me` hydrate.
2. Email auth + OTP screens, forgot/reset password.
3. Google sign-in (+ Apple on iOS).
4. Guest → account migration; CV & cover letter lists from server (duplicate, delete, ATS badge).
5. Dashboard (`/portal/stats` + recent activity).
6. Public profile editor + share URL.
7. Inbox.
8. FCM push: token register/unregister, foreground/background/terminated handling, `cv://inbox/{id}` deep link.
9. Notification center (bell + list + mark read) and notification settings.
10. Extra locales.
11. Account settings / delete account (after backend endpoints ship).

---

## 10. Backend items the mobile dev should know about (not mobile work)

Tracked on the backend side — **do not build around them**:

| Issue | Impact on mobile |
|-------|------------------|
| No endpoints for **update profile**, **change password**, **delete account** (web settings page calls non-existent routes). | Account settings screen is blocked until added. |
| No **guest claim/merge** endpoint. | Use the re-POST workaround in section 4. |
| `POST /cover-letters/{id}/duplicate` not deployed yet. | Hold that button until release. |
| Cover letter `language` limited to `en, ar, tr`. | Don't offer other languages for cover letters yet. |
| **FCM sending is not active yet.** It requires `FIREBASE_CREDENTIALS` (service account JSON, same Firebase project as the app) and the `google/apiclient` package — neither is set up yet — plus a running queue worker. Until then pushes are silently skipped. | Build the mobile side now (token register, handlers, deep link). End-to-end push testing waits for the backend setup. The in-app notification center already works. |
| Push title/body text is not localized per user (title = app name). | Expected for now. |
