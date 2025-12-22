# CV Builder API - Developer Documentation

## Table of Contents
1. [Project Overview](#project-overview)
2. [Architecture](#architecture)
3. [Database Schema](#database-schema)
4. [Key Concepts](#key-concepts)
5. [Important Notes for Developers](#important-notes-for-developers)

---

## Project Overview

This is a **Laravel-based REST API** for a CV (Curriculum Vitae) and Cover Letter Builder application. The API serves as the backend for a Flutter mobile application, providing endpoints for:

- **User Authentication** (Registration, Login, Password Reset)
- **CV Management** (Create, Read, Update, Delete CVs/Profiles)
- **Template Management** (CV template discovery and selection)
- **PDF Generation** (Server-side PDF generation from CV data using templates)
- **Multi-language Support** (English, Arabic, Turkish)

### Technology Stack

- **Framework**: Laravel 11.x
- **Database**: MySQL/PostgreSQL (via Eloquent ORM)
- **Authentication**: Laravel Sanctum (Token-based)
- **Admin Panel**: Filament PHP
- **PDF Generation**: Spatie Laravel PDF (Browsershot/Chromium)
- **Architecture Pattern**: Repository Pattern + Service Layer

### Project Structure

```
app/
├── Enums/              # Enumerations (UserType, etc.)
├── Filament/           # Admin panel resources and pages
├── Http/
│   ├── Controllers/    # API controllers
│   ├── Middleware/     # Custom middleware
│   └── Requests/       # Form request validation
├── Livewire/           # Livewire components
├── Models/             # Eloquent models
├── Providers/          # Service providers
├── Repositories/       # Data access layer (Repository Pattern)
└── Services/           # Business logic layer
```

---

## Architecture

### Design Patterns

#### 1. Repository Pattern
- **Interface**: `App\Repositories\CVRepositoryInterface`
- **Implementation**: `App\Repositories\CVRepository`
- **Purpose**: Abstracts database operations, making the codebase testable and maintainable
- **Usage**: Controllers inject `CVRepositoryInterface` instead of directly using models

#### 2. Service Layer
- **Services**: 
  - `CVPDFService`: Handles PDF generation logic
  - `CVDataMapper`: Maps between API format and database format
- **Purpose**: Encapsulates business logic separate from controllers

#### 3. Dependency Injection
- All dependencies are injected via constructor
- Repository interface is bound in `AppServiceProvider`

### Request Flow

```
API Request → Controller → Service/Repository → Model → Database
                                    ↓
                              Response/PDF
```

### Key Components

1. **Controllers** (`app/Http/Controllers/Api/`)
   - `AuthController`: Authentication endpoints
   - `CVController`: CV CRUD and PDF generation
   - `ShareController`: Public sharing endpoints

2. **Repositories** (`app/Repositories/`)
   - `CVRepository`: Implements `CVRepositoryInterface`
   - Handles all database queries for CVs/Profiles

3. **Services** (`app/Services/`)
   - `CVPDFService`: Unified PDF generation (download or URL)
   - `CVDataMapper`: Converts between API `user_data` format and Profile JSON structure

4. **Models** (`app/Models/`)
   - `User`: User accounts with roles (Admin/User)
   - `Profile`: CV/Resume data (stores JSON columns)
   - `Template`: CV templates for PDF generation

---

## Database Schema

### Tables Overview

| Table | Purpose | Key Features |
|-------|---------|--------------|
| `users` | User accounts | Authentication, roles, soft deletes |
| `profiles` | CV/Resume data | JSON columns, nullable user_id (for unauthenticated CVs) |
| `templates` | CV templates | Active/inactive flag, soft deletes |
| `personal_access_tokens` | Sanctum tokens | API authentication |
| `cache`, `jobs`, `sessions` | Laravel system tables | Standard Laravel tables |

---

### 1. `users` Table

**Purpose**: Stores user accounts for authentication and authorization.

**Schema**:
```sql
id                  BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT
name                VARCHAR(255) NOT NULL
email               VARCHAR(255) UNIQUE NOT NULL
email_verified_at   TIMESTAMP NULL
password            VARCHAR(255) NOT NULL
remember_token      VARCHAR(100) NULL
first_name          VARCHAR(255) NULL          -- Added via migration
last_name           VARCHAR(255) NULL          -- Added via migration
phone               VARCHAR(255) NULL          -- Added via migration
type                VARCHAR(255) NULL          -- Enum: 'admin' or 'user'
active              BOOLEAN DEFAULT TRUE        -- Account status
created_at          TIMESTAMP
updated_at          TIMESTAMP
deleted_at          TIMESTAMP NULL             -- Soft deletes
```

**Key Points**:
- `type` is cast to `App\Enums\UserType` enum in the model
- `active` flag controls account access
- Soft deletes enabled (records are not permanently deleted)
- Relationship: `hasMany(Profile::class)` - one user can have multiple CVs

**Model**: `App\Models\User`

**Enum Values** (`App\Enums\UserType`):
- `ADMIN = 'admin'` → Admin users (can access Filament dashboard)
- `USER = 'user'` → Regular users (API access only)

---

### 2. `profiles` Table

**Purpose**: Stores CV/Resume data. This is the core table of the application.

**Schema**:
```sql
id              BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT
user_id         BIGINT UNSIGNED NULL FOREIGN KEY → users.id
name            VARCHAR(255) NOT NULL                    -- CV name/title
language        VARCHAR(10) DEFAULT 'en'                 -- Language code (en, ar, tr)
sections_order  JSON NULL                                -- Array of section names in display order
interests       JSON NULL                                -- Array of interest objects
languages       JSON NULL                                -- Array of language objects
info            JSON NULL                                -- Personal information object
experiences     JSON NULL                                -- Array of experience objects
projects        JSON NULL                                -- Array of project objects
educations      JSON NULL                                -- Array of education objects
created_at      TIMESTAMP
updated_at      TIMESTAMP
deleted_at      TIMESTAMP NULL                           -- Soft deletes
```

**Key Points**:
- **`user_id` is NULLABLE**: Allows unauthenticated users to create temporary CVs
- All data sections are stored as JSON columns (flexible schema)
- `sections_order` controls the display order of sections in the PDF
- Soft deletes enabled

**JSON Column Structures**:

#### `info` (Personal Information)
```json
{
  "firstName": "John",
  "lastName": "Doe",
  "jobTitle": "Senior Developer",
  "email": "john@example.com",
  "address": "123 Street, City",
  "portfolioUrl": "https://portfolio.com",
  "phone": "+1234567890",
  "summary": "Professional summary text",
  "birthdate": "1990-01-01",
  "skills": [
    {"name": "PHP"},
    {"name": "Laravel"}
  ]
}
```

#### `educations` (Education History)
```json
[
  {
    "institution": "University Name",
    "degree": "Bachelor of Science",
    "fieldOfStudy": "Computer Science",
    "description": "Optional description",
    "from": "2010-09",
    "to": "2014-06"
  }
]
```

#### `experiences` (Work Experience)
```json
[
  {
    "position": "Senior Developer",
    "name": "Company Name",           // Note: API uses "company", DB uses "name"
    "location": "City, Country",
    "description": "Job description",
    "from": "2020-01",
    "to": "2023-12",
    "currentlyWorkingHere": false     // Note: API uses "current", DB uses "currentlyWorkingHere"
  }
]
```

#### `projects` (Projects Portfolio)
```json
[
  {
    "name": "Project Title",          // Note: API uses "title", DB uses "name"
    "description": "Project description",
    "url": "https://project-url.com",
    "from": "2022-01",
    "to": "2022-12"
  }
]
```

#### `languages` (Language Skills)
```json
[
  {
    "language": "English",            // Note: API uses "name", DB uses "language"
    "level": "fluent"                 // Note: API uses "proficiencyLevel" (1-5), DB uses "level" (string)
  }
]
```

**Level Mapping** (API ↔ Database):
- API: `1` = beginner, `2` = intermediate, `3` = advanced, `4` = fluent, `5` = native
- DB: `"beginner"`, `"intermediate"`, `"advanced"`, `"fluent"`, `"native"`

#### `interests` (Personal Interests)
```json
[
  {
    "interest": "Reading"             // Note: API uses "name", DB uses "interest"
  }
]
```

#### `sections_order` (Display Order)
```json
[
  "Personal Information",
  "Skills",
  "Education",
  "Experience",
  "Projects",
  "Languages",
  "Interests"
]
```

**Model**: `App\Models\Profile`

**Important**: The `CVDataMapper` service handles conversion between API format (`user_data`) and Profile JSON structure. Always use the mapper when working with Profile data.

---

### 3. `templates` Table

**Purpose**: Stores CV templates available for PDF generation.

**Schema**:
```sql
id              BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT
name            VARCHAR(255) NOT NULL                    -- Template name (e.g., "Modern", "Classic")
preview         VARCHAR(255) NOT NULL                    -- Preview image URL/path
description     TEXT NULL                                -- Template description
is_active       BOOLEAN DEFAULT TRUE                     -- Whether template is available
created_at      TIMESTAMP
updated_at      TIMESTAMP
deleted_at      TIMESTAMP NULL                           -- Soft deletes
```

**Key Points**:
- Only active templates (`is_active = true`) are used for PDF generation
- Template name is converted to view path: `"Modern Template"` → `"templates.cv.modern-template"`
- Views are located in `resources/views/templates/cv/`

**Model**: `App\Models\Template`

**View Naming Convention**:
- Template name: `"Modern Template"`
- View path: `templates.cv.modern-template` (kebab-case, lowercase)
- File location: `resources/views/templates/cv/modern-template.blade.php`

---

### 4. `personal_access_tokens` Table

**Purpose**: Laravel Sanctum token storage for API authentication.

**Schema**:
```sql
id              BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT
tokenable_type  VARCHAR(255) NOT NULL
tokenable_id    BIGINT UNSIGNED NOT NULL
name            VARCHAR(255) NOT NULL
token           VARCHAR(64) UNIQUE NOT NULL
abilities       TEXT NULL
last_used_at    TIMESTAMP NULL
expires_at      TIMESTAMP NULL
created_at      TIMESTAMP
updated_at      TIMESTAMP
```

**Usage**: Managed by Laravel Sanctum. Users receive tokens after login for authenticated API requests.

---

## Key Concepts

### 1. Data Mapping (API ↔ Database)

The API uses a different structure than the database for CV data:

**API Format** (`user_data` in requests):
```json
{
  "firstName": "John",
  "experiences": [{"company": "ACME", "current": true}],
  "projects": [{"title": "Project"}],
  "languages": [{"name": "English", "proficiencyLevel": 4}],
  "interests": [{"name": "Reading"}]
}
```

**Database Format** (Profile JSON columns):
```json
{
  "info": {"firstName": "John"},
  "experiences": [{"name": "ACME", "currentlyWorkingHere": true}],
  "projects": [{"name": "Project"}],
  "languages": [{"language": "English", "level": "fluent"}],
  "interests": [{"interest": "Reading"}]
}
```

**Solution**: `CVDataMapper` service handles all conversions:
- `mapUserDataToProfile()`: API → Database
- `mapProfileToUserData()`: Database → API
- `formatProfileResponse()`: Full Profile → API response format

**Always use `CVDataMapper` when converting between formats!**

### 2. PDF Generation

**Service**: `App\Services\CVPDFService`

**Unified Method**:
```php
generatePdf(Profile $profile, Template $template, bool $returnUrl = false): Response|string
```

**Behavior**:
- `$returnUrl = false`: Returns download response (PDF file)
- `$returnUrl = true`: Saves PDF to `storage/app/public/cvs/` and returns URL string

**Usage in API**:
- Query parameter `return_url=1` → Returns JSON with URL
- No parameter or `return_url=0` → Returns PDF download

**Requirements**:
- Node.js and npm installed (configured via `LARAVEL_PDF_NODE_BINARY`, `LARAVEL_PDF_NPM_BINARY`)
- Chromium/Chrome installed (configured via `LARAVEL_PDF_CHROME_PATH`)
- Environment variable `LARAVEL_PDF_NO_SANDBOX=true` for server environments

### 3. Authentication

**Method**: Laravel Sanctum (Token-based)

**Endpoints**:
- `POST /api/v1/auth/register` - Create account
- `POST /api/v1/auth/login` - Get token
- `POST /api/v1/auth/logout` - Revoke token (requires auth)
- `GET /api/v1/auth/me` - Get current user (requires auth)

**Middleware**: `auth:sanctum` protects routes

**Token Usage**: Include in request header:
```
Authorization: Bearer {token}
```

### 4. Unauthenticated CV Creation

**Feature**: Users can create CVs without authentication.

**Implementation**:
- `profiles.user_id` is nullable
- `POST /api/v1/cvs` accepts requests without authentication
- If `template_id` is provided, returns PDF directly
- If authenticated, CV is associated with user account

### 5. Multi-language Support

**Supported Languages**: `en` (English), `ar` (Arabic), `tr` (Turkish)

**Storage**:
- Profile `language` column stores language code
- Translation files in `resources/lang/{locale}/`

**API Usage**:
- Filter CVs by language: `GET /api/v1/cvs?language=en`
- Set language when creating CV: `POST /api/v1/cvs` with `"language": "ar"`

### 6. Filament Admin Panel

**Purpose**: Administrative interface for managing users, profiles, and templates.

**Access**: Only users with `type = 'admin'` can access `/admin`

**Resources**:
- `UserResource`: Manage users
- `ProfileResource`: View/manage CVs
- `TemplateResource`: Manage templates
- `PrintProfile` page: Generate PDFs from admin panel (uses URL mode)

---

## Important Notes for Developers

### ⚠️ Critical Rules

1. **Always Use CVDataMapper**
   - Never manually convert between API format and Profile JSON
   - Use `CVDataMapper::mapUserDataToProfile()` when saving
   - Use `CVDataMapper::mapProfileToUserData()` when reading

2. **Field Name Differences**
   - API uses `company`, DB uses `name` (experiences)
   - API uses `title`, DB uses `name` (projects)
   - API uses `current`, DB uses `currentlyWorkingHere` (experiences)
   - API uses `name`, DB uses `language` (languages)
   - API uses `proficiencyLevel` (1-5), DB uses `level` (string)
   - API uses `name`, DB uses `interest` (interests)

3. **PDF Generation**
   - Always check if template is active before generating PDF
   - View path is derived from template name (kebab-case, lowercase)
   - PDF files are stored in `storage/app/public/cvs/` when using URL mode
   - Ensure storage link is created: `php artisan storage:link`

4. **Repository Pattern**
   - Controllers should inject `CVRepositoryInterface`, not use models directly
   - All database queries should go through repositories
   - This makes testing easier and code more maintainable

5. **Soft Deletes**
   - All main models use soft deletes (`deleted_at` column)
   - Use `withTrashed()` or `onlyTrashed()` if you need deleted records
   - `delete()` method soft deletes by default

6. **User Types**
   - Always check `UserType` enum, not string comparisons
   - Use `$user->isAdmin()` or `$user->isUser()` helper methods
   - Admin users can access Filament dashboard

7. **Nullable user_id**
   - `profiles.user_id` can be NULL for unauthenticated CVs
   - Always check for null when accessing `$profile->user_id`
   - Foreign key constraint allows NULL values

8. **Environment Configuration**
   - PDF generation requires Node.js, npm, and Chromium paths in `.env`
   - Production servers need `LARAVEL_PDF_NO_SANDBOX=true`
   - Clear config cache after changing `.env`: `php artisan config:clear`

### 📝 Code Examples

#### Creating a CV via API Format
```php
use App\Services\CVDataMapper;
use App\Repositories\CVRepositoryInterface;

$dataMapper = app(CVDataMapper::class);
$repository = app(CVRepositoryInterface::class);

$apiData = [
    'firstName' => 'John',
    'experiences' => [['company' => 'ACME', 'current' => true]]
];

$mappedData = $dataMapper->mapUserDataToProfile($apiData);
$profile = $repository->create([
    'user_id' => auth()->id(),
    'name' => 'My CV',
    'language' => 'en',
    ...$mappedData
]);
```

#### Generating PDF
```php
use App\Services\CVPDFService;

$pdfService = app(CVPDFService::class);
$template = Template::where('is_active', true)->first();

// Download mode
$response = $pdfService->generatePdf($profile, $template, false);

// URL mode
$url = $pdfService->generatePdf($profile, $template, true);
```

#### Using Repository
```php
use App\Repositories\CVRepositoryInterface;

$repository = app(CVRepositoryInterface::class);

// Get user's CVs
$cvs = $repository->getAllForUser(auth()->id(), 'en');

// Find CV
$cv = $repository->findByIdForUser($id, auth()->id());
```

### 🔍 Common Pitfalls

1. **Forgetting to use CVDataMapper**: Leads to field name mismatches
2. **Hardcoding field names**: API and DB use different names
3. **Not checking template active status**: Inactive templates cause errors
4. **Missing storage link**: PDF URLs won't work without `storage:link`
5. **Direct model usage in controllers**: Breaks repository pattern
6. **String comparison for UserType**: Use enum or helper methods

### 🚀 Development Workflow

1. **Local Development**:
   - Use Laragon/XAMPP for local server
   - Node.js and Chromium should be installed locally
   - Run migrations: `php artisan migrate`
   - Seed templates: `php artisan db:seed --class=TemplateSeeder`

2. **Testing API**:
   - Use Postman collection: `CV_API_Collection.postman_collection.json`
   - Base URL: `http://localhost/api/v1`
   - Test both authenticated and unauthenticated endpoints

3. **Admin Panel**:
   - Access: `http://localhost/admin`
   - Create admin user via seeder or manually
   - Manage users, profiles, and templates

### 📚 Additional Resources

- **API Documentation**: See `api_prd.md` for detailed endpoint specifications
- **Postman Collection**: `CV_API_Collection.postman_collection.json` for API testing
- **Schema Reference**: `schema.json` (may be outdated, refer to migrations)

---

## Summary

This API follows a **Repository Pattern + Service Layer** architecture, uses **JSON columns** for flexible CV data storage, and provides **unified PDF generation** that can return either downloads or URLs. The key to working with this codebase is understanding the **data mapping** between API format and database format, which is handled by the `CVDataMapper` service.

Always use repositories for data access, services for business logic, and the data mapper for format conversions. This ensures consistency and maintainability across the codebase.

