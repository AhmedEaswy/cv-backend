# PRD: Laravel API for CV & Cover Letter Builder

## Goals
- Provide REST API backing the Flutter app screens for CV management, section inputs, template selection, PDF generation, cover letters, and support pages.
- Persist user data, support localization, and enable server-side PDF generation and sharing.

## Scope
- Auth (optional for MVP): email/password JWT.
- CV CRUD with nested sections: personal info, education, experience, projects, skills, languages, interests, sections order.
- Templates discovery.
- PDF generation (CV and Cover Letters).
- Cover Letter CRUD.
- Contact form submissions.
- User preferences (locale, theme, experimental flags).

## Entities
- User
- CV
- Education
- Experience
- Project
- Skill
- Language
- Interest
- CoverLetter
- Template
- ContactMessage

## Data Models
- CV
  ```json
  {
    "id": "uuid",
    "user_id": "uuid",
    "name": "My CV",
    "language": "en",
    "sections_order": [
      "Personal Information","Skills","Education","Experience","Projects","Languages","Interests"
    ],
    "user_data": {
      "firstName": "John",
      "lastName": "Doe",
      "jobTitle": "Flutter Developer",
      "email": "john@example.com",
      "address": "optional",
      "portfolioUrl": "optional",
      "phone": "optional",
      "summary": "optional",
      "birthdate": "YYYY-MM-DD",
      "skills": [{"name": "Flutter"}],
      "educations": [{"institution": "Uni", "degree": "BSc", "fieldOfStudy": "CS", "from": "YYYY-MM", "to": "YYYY-MM"}],
      "experiences": [{"position": "Engineer", "company": "ACME", "description": "...", "from": "YYYY-MM", "to": "YYYY-MM", "current": false}],
      "projects": [{"title": "Portfolio", "description": "...", "technologies": "Flutter,Dart", "url": "https://...", "from": "YYYY-MM", "to": "YYYY-MM", "current": false}],
      "languages": [{"name": "English", "proficiencyLevel": 4}],
      "interests": [{"name": "Reading"}]
    },
    "created_at": "ISO8601",
    "updated_at": "ISO8601"
  }
  ```
- CoverLetter
  ```json
  {
    "id": "uuid",
    "user_id": "uuid",
    "recipientName": "Dr. Smith",
    "companyName": "ACME",
    "position": "Software Developer",
    "introduction": "...",
    "body": "...",
    "closing": "...",
    "includeWithCV": false,
    "created_at": "ISO8601",
    "updated_at": "ISO8601"
  }
  ```

## Endpoints
- Auth
  - `POST /auth/register` {email,password,name}
  - `POST /auth/login` {email,password}
  - `POST /auth/logout`
  - `GET /auth/me`
- CVs
  - `GET /cvs` list current user's CVs (filters: `language`)
  - `POST /cvs` create CV {name, language, user_data?, sections_order?}
  - `GET /cvs/{id}`
  - `PUT /cvs/{id}` update metadata and `user_data`
  - `DELETE /cvs/{id}`
  - `POST /cvs/{id}/duplicate` duplicate CV
  - `PUT /cvs/{id}/sections-order` {sections_order: [..]}
  - Nested resources (alternative to full `user_data` updates):
    - Education: `POST /cvs/{id}/educations`, `PUT /cvs/{id}/educations/{eduId}`, `DELETE /cvs/{id}/educations/{eduId}`
    - Experience: `POST /cvs/{id}/experiences`, `PUT /cvs/{id}/experiences/{expId}`, `DELETE /cvs/{id}/experiences/{expId}`
    - Projects: `POST /cvs/{id}/projects`, `PUT /cvs/{id}/projects/{projId}`, `DELETE /cvs/{id}/projects/{projId}`
    - Skills: `POST /cvs/{id}/skills`, `DELETE /cvs/{id}/skills/{skillId}`
    - Languages: `POST /cvs/{id}/languages`, `PUT /cvs/{id}/languages/{langId}`, `DELETE /cvs/{id}/languages/{langId}`
    - Interests: `POST /cvs/{id}/interests`, `DELETE /cvs/{id}/interests/{interestId}`
- Templates
  - `GET /templates` returns available CV templates {id,name,type,preview_url}
  - `GET /templates/{id}`
- PDF Generation
  - `POST /cvs/{id}/pdf` -> returns application/pdf; body may include `{templateId, locale}`
  - `POST /cover-letters/{id}/pdf` -> returns application/pdf
  - `POST /cvs/{id}/share` -> server-side share options (optional)
- Cover Letters
  - `GET /cover-letters`
  - `POST /cover-letters`
  - `GET /cover-letters/{id}`
  - `PUT /cover-letters/{id}`
  - `DELETE /cover-letters/{id}`
- Contact
  - `POST /contact` {email,message,platform}
- Preferences
  - `PATCH /users/me/preferences` {locale, theme, experimentalLanguages}

## Validation Rules
- Personal info: `firstName,lastName,jobTitle,email` required; email pattern.
- Education/Experience/Projects: `from <= to` unless `current=true`.
- Language proficiency: integer 1–5.
- URL fields: valid URL.
- Sections order: must be permutation of the supported sections in `lib/src/features/user_info_input/presenter/view/order_and_view_inputs.dart:16-27`.

## Authentication & Security
- JWT tokens (Laravel Sanctum/Passport).
- Rate limiting, IP throttling on `POST /contact`.
- CORS for Flutter web.
- Access control: only owner can mutate CVs/cover letters.

## Localization
- Accept `Accept-Language` header and/or `locale` request param.
- Supported: `en`, `ar`, `tr` (aligns with app options in `lib/src/features/cv_management/ui/cv_list_view.dart:270-337`).
- RTL/LTR handled server-side for PDF generation where relevant.

## Error Format
```json
{
  "error": {
    "code": "validation_failed",
    "message": "...",
    "fields": {"email": "invalid"}
  }
}
```

## Non-Functional Requirements
- Performance: PDF generation under 2s for typical CV; pagination for lists.
- Observability: request logging, error tracking.
- Storage: files (generated PDFs) stored in S3-compatible storage (optional), presigned URLs for download.

## Milestones
- M1: Auth + CV CRUD (top-level `user_data`) + Templates list.
- M2: Nested resources + sections order + Cover Letters CRUD.
- M3: PDF generation endpoints + contact form + preferences.

## Mapping to App Screens
- CV list and creation → `/cvs` CRUD (`lib/src/features/cv_management/ui/cv_list_view.dart:228-378`).
- Section inputs → nested resources and/or `PUT /cvs/{id}` with `user_data`.
- Order & preview → `PUT /cvs/{id}/sections-order`.
- Template selection → `GET /templates` then `POST /cvs/{id}/pdf` with `templateId`.
- PDF preview/share → server returns PDF; client saves/shares (`lib/src/features/pdf_preview/presenter/view.dart:61-71,106-142`).
- Cover letter input/preview → Cover Letter CRUD + PDF (`lib/src/features/cover_letter/cover_letter_view.dart:212-235`, `cover_letter_preview_page.dart:45-55`).