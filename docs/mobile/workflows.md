# Mobile workflows

Recommended API sequences for common mobile screens.

---

## 1. Cold start / session restore

1. Read token from secure storage
2. If missing → auth screens
3. If present → `GET /auth/me`
   - **200** → home / portal
   - **401** → clear token → auth screens

Optional parallel: `GET /portal/stats` for dashboard widgets.

---

## 2. Email auth

**Sign up:** `POST /auth/register` → store `result.token` → home  
**Sign in:** `POST /auth/login` → store token → home  
**Sign out:** `POST /auth/logout` → clear storage → auth

---

## 3. Google Sign-In

1. Native Google Sign-In → Google access token
2. `POST /auth/google` with `{ "code": "<access_token>" }`
3. Store `result.token`
4. Continue as logged-in

Do not use the browser redirect/callback endpoints unless you intentionally embed a WebView OAuth flow.

---

## 4. CV library

1. `GET /cvs?language=en` (optional filter)
2. Tap item → `GET /cvs/{id}`
3. Edit → `PUT /cvs/{id}` with full or partial `user_data`
4. Duplicate → `POST /cvs/{id}/duplicate`
5. Delete → `DELETE /cvs/{id}`
6. Export → `POST /cvs/print` `{ template_id, profile_id }` → open `result.url`

---

## 5. Create CV (authenticated)

1. `GET /shares/templates` → template picker (`preview`, `supports_image`, `is_default`)
2. Build form state as `user_data` (+ `name`, `language`, `sections_order`)
3. Optional live ATS: `POST /cvs/ats-check` with `user_data` (+ `job_description`)
4. Save: `POST /cvs` with Bearer → store returned `id`
5. Export: `POST /cvs/print`

Guest mode (no account): `POST /cvs` with `template_id` + `user_data` returns PDF `{ url, profile_id }` without requiring login. Send a stable install UUID as `X-Anonymous-Id` (or body `anonymous_id`) and the local document id as `client_ref` so reprints update the same profile.

---

## 6. Cover letter

1. `GET /cover-letters/templates`
2. Compose `user_data` + `language` + selected `cover_letter_template_id`
3. Save: `POST /cover-letters` (or `PUT` when editing)
4. Optional WebView preview: `GET /cover-letter/{id}?template_id=…`
5. Export: `POST /cover-letters/print`
6. Library: `GET /cover-letters` → show / update / delete

---

## 7. ATS from PDF

1. Pick PDF from device
2. `POST /cvs/ats-check/upload` multipart field `file` (+ optional `job_description`, `language`)
3. Render `score`, `grade`, `checks[]`, optional `keywords`

Structured path: `POST /cvs/ats-check` with either `profile_id` or in-progress `user_data`.

---

## 8. Public profile

1. `GET /public-profiles` — if 404, show create flow
2. `GET /public-profiles/templates`
3. Create: `POST /public-profiles` (409 if already exists) — include `enable_contact_form`, `enable_subdomain`, `user_data.seo`, `user_data.socialLinks` as needed
4. Update: `PUT /public-profiles`
5. Share `result.public_url` (path `/u/{slug}` or `{slug}.{domain}` when subdomain enabled)
6. Delete: `DELETE /public-profiles`

### Inbox

1. Badge: `GET /portal/stats` → `unread_messages` / `unread_notifications`
2. List: `GET /public-profiles/inbox`
3. Mark read: `POST /public-profiles/inbox/{id}/read`
4. Report spam: `POST /public-profiles/inbox/{id}/spam` (blocks sender platform-wide)
5. Reply: `POST /public-profiles/inbox/{id}/reply` with `{ body }` — returns pending reply; queue worker sends mail
6. Retry failed: `POST /public-profiles/inbox/{id}/replies/{replyId}/retry`

### Notifications & push

1. Register FCM: `POST /devices/push-token` `{ token, platform: ios|android }`
2. Unregister on logout: `DELETE /devices/push-token` `{ token }`
3. In-app feed: `GET /notifications`; mark read / read-all
4. Prefs: `GET/PUT /settings/notifications` (`notify_contact_email`, `notify_contact_push`)
5. Optional custom From: `GET/PUT /settings/outbound-mail` + verify-dns / test

Requires a running queue worker for reply email and FCM delivery.

---

## 9. Home / portal dashboard

```http
GET /portal/stats
Authorization: Bearer …
```

Drive cards from counts + `latest_cv` / `latest_cover_letter` + public profile flags.

---

## 10. AI settings (settings screen)

1. `GET /ai-settings` → show provider list / models / `has_api_key`
2. Save: `PUT /ai-settings`
3. Clear key: `{ "clear_api_key": true, "provider": "…" }`

Never expect the API to return the raw key.

---

## 11. Store badge analytics (optional)

On App Store / Play Store tap:

```http
POST /api/v1/analytics/click
{ "target": "app_store", "page": "mobile_home" }
```

Expect **204**. Fire-and-forget; do not block navigation.

---

## Error handling tips

| Situation | Client action |
|-----------|---------------|
| 401 | Clear token, route to login |
| 403 on login | Show “account inactive” |
| 404 on owned resource | Treat as missing / refresh list |
| 409 on public profile create | Switch to update flow |
| 422 | Map `errors` to form fields |
| Print 500 | Retry / show PDF generation failure |

---

## Offline / sync notes

- There is no sync/delta API; use full list + show.
- Soft deletes mean deleted IDs disappear from lists.
- PDF URLs are absolute storage URLs; cache carefully (they may be regenerated).
