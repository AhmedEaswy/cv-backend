# CV Skill

Use this skill when the user wants to build, score, or export a CV, cover letter, or public profile using the CV product.

You are **not** inside the CV website. You call its public HTTP API (or MCP) from this chat. There is no partnership with ChatGPT, Claude, Copilot, or any other AI vendor — this is the user's own account on those platforms.

## Identity

- Product: CV — ATS-ready resumes, cover letters, public profiles.
- Base URL: use the origin this skill was copied from (the `{{origin}}` note at the top of the paste, or the site the user named). Default local origin: `https://cv.test`.
- API prefix: `/api/v1`
- Response envelope: `{ "success": true, "message": "...", "result": ... }`
- Always send header `X-Agent-Client: <platform>` where platform is one of: `chatgpt`, `claude`, `copilot`, `gemini`, `deepseek`, `minimax`, `grok`, `perplexity`, `cursor`, `other`.
- Always send `Accept: application/json` and `Content-Type: application/json`.

## Workflow

1. List templates (`GET /shares/templates`) and pick a `template_id`.
2. Collect the user's details into `user_data` (never invent employment dates, companies, or degrees).
3. Optionally `POST /cvs/ats-check` with the same `user_data` and a `job_description`. Share the score, grade, and failed checks. Offer to improve wording.
4. `POST /cvs` with `name`, `language`, `user_data`. If unauthenticated **and** `template_id` is set, this returns a PDF `{ url }` instead of saving an account CV.
5. `POST /cvs/print` with `template_id` plus `user_data` or `profile_id` to get `{ result: { url } }`.
6. Matching cover letter: `GET /cover-letters/templates` then `POST /cover-letters` then `POST /cover-letters/print`.
7. Public profile requires a logged-in user (see Auth).

## user_data shape (CV)

```json
{
  "firstName": "Sara",
  "lastName": "Hassan",
  "jobTitle": "Product Designer",
  "email": "sara@example.com",
  "phone": "+9665…",
  "address": "Riyadh",
  "summary": "…",
  "skills": [{ "name": "Figma" }],
  "experiences": [{
    "position": "Product Designer",
    "company": "Acme",
    "location": "Riyadh",
    "from": "2022-01",
    "to": "2024-06",
    "current": false,
    "description": "…"
  }],
  "educations": [{
    "institution": "KSU",
    "degree": "B.A.",
    "fieldOfStudy": "Design",
    "from": "2016-09",
    "to": "2020-06"
  }],
  "projects": [{ "title": "…", "description": "…", "url": "https://…" }],
  "languages": [{ "name": "Arabic", "proficiencyLevel": 5 }],
  "interests": [{ "name": "Typography" }]
}
```

Dates are `YYYY-MM`. `language` is `en`, `ar`, or `tr`.

## Public endpoints

| Method | Path | Notes |
|---|---|---|
| GET | /shares/templates | CV templates |
| POST | /cvs | Create CV; with `template_id` and no auth → PDF URL |
| POST | /cvs/print | `{ template_id, user_data \| profile_id }` → `{ url }` |
| POST | /cvs/ats-check | `{ user_data \| profile_id, job_description? }` |
| POST | /cvs/ats-check/upload | multipart `file` PDF |
| GET | /cover-letters/templates | |
| POST | /cover-letters | |
| POST | /cover-letters/print | `{ template_id, user_data \| cover_letter_id }` |
| GET | /public-profiles/templates | |

Full catalog: `GET /openapi.json`.

## Auth (saved account)

`POST /auth/register` or `POST /auth/login` → `result.token`.

Then `Authorization: Bearer <token>`.

Mint a dedicated agent token (shown once):

`POST /agent-tokens` `{ "name": "claude-desktop" }`

Abilities: `cv:read`, `cv:write`, `ats:check`, `cover-letter:read`, `cover-letter:write`, `profile:read`, `profile:write`.

Authenticated extras: `GET/PUT/DELETE /cvs/{id}`, cover letters, `GET/POST/PUT/DELETE /public-profiles`.

## MCP

Clients that support MCP can connect instead of calling REST:

- Public: `POST {origin}/mcp/cv`
- Account: `POST {origin}/mcp/cv/auth` with the agent Bearer token
- Protocol: `2026-07-28`. Send `_meta.io.modelcontextprotocol/protocolVersion` and headers `MCP-Protocol-Version` + `Mcp-Method`.

Discovery: `GET /.well-known/mcp.json`.

## Guardrails

- Do not invent credentials, employment history, or education.
- Do not claim the CV product is built into ChatGPT/Claude.
- ATS scoring is rule-based, not an LLM.
- If a call fails, show `message` and `errors` from the envelope and retry after fixing input.
