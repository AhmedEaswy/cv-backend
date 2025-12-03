# CV API Collection Documentation

Complete documentation for all endpoints in the CV API Postman Collection.

## Table of Contents

- [Collection Overview](#collection-overview)
- [Getting Started](#getting-started)
- [Authentication](#authentication)
- [Folders](#folders)
  - [Auth](#auth)
  - [CVs](#cvs)
  - [Shares](#shares)

---

## Collection Overview

**Collection Name:** CV API Collection  
**Description:** Authentication + CV endpoints for the resume builder API.  
**Base URL:** `{{base_url}}` (default: `http://localhost:8000`)  
**API Version:** v1  
**Collection Link:** [Postman Collection](https://universal-meadow-494559.postman.co/workspace/My-Workspace~7b079930-7ccd-457c-81fe-aa3380fe65dc/collection/15765892-e649d885-7885-4042-8a7a-7c65c1c9c0ce)

### Collection Variables

- `base_url`: Base URL for API requests (default: `http://localhost:8000`)
- `auth_token`: Bearer token for authentication (set automatically after login)

---

## Getting Started

### Prerequisites

1. Import the Postman collection into Postman
2. Set up the `base_url` variable in your Postman environment
3. For authenticated requests, use the Login endpoint first

### Authentication Setup

The collection uses Bearer token authentication. After logging in:

1. The token is automatically extracted from the Login/Register response
2. It's stored in the `auth_token` environment variable
3. The collection's pre-request script automatically adds it to all requests as `Authorization: Bearer {token}`

---

## Authentication

The collection automatically handles authentication via pre-request scripts that add the `Authorization` header using the stored `auth_token` variable.

### Pre-Request Script

All requests automatically include:
```javascript
Authorization: Bearer {auth_token}
```

---

## Folders

## Auth

The **Auth** folder contains all authentication-related endpoints. This includes user registration, login, logout, password reset functionality, and retrieving the authenticated user's profile.

**Folder Description:** User authentication and account management endpoints.

### Register

**Method:** `POST`  
**Endpoint:** `/api/v1/auth/register`  
**Authentication:** Not required

#### Description
Create a new user account. Automatically receives an API token in the response.

#### Headers
```
Content-Type: application/json
Accept: application/json
```

#### Request Body
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password",
  "password_confirmation": "password"
}
```

#### Request Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| name | string | Yes | User's full name |
| email | string | Yes | User's email address |
| password | string | Yes | User's password |
| password_confirmation | string | Yes | Password confirmation (must match password) |

#### Response
Returns user data along with an authentication token. The token is automatically stored in the `auth_token` collection variable.

#### Test Script
The Auth folder includes a test script that automatically extracts the token from the response and stores it in the `auth_token` environment variable.

---

### Login

**Method:** `POST`  
**Endpoint:** `/api/v1/auth/login`  
**Authentication:** Not required

#### Description
Login with email & password. Copy the token field from the response into the `auth_token` collection variable.

#### Headers
```
Content-Type: application/json
Accept: application/json
```

#### Request Body
```json
{
  "email": "user@app.com",
  "password": "123456789"
}
```

#### Request Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| email | string | Yes | User's email address |
| password | string | Yes | User's password |

#### Response
Returns user data along with an authentication token. The token is automatically extracted and stored in the `auth_token` collection variable for subsequent requests.

---

### Logout

**Method:** `POST`  
**Endpoint:** `/api/v1/auth/logout`  
**Authentication:** Required

#### Description
Invalidate the current token.

#### Headers
```
Accept: application/json
Authorization: Bearer {auth_token}
```

#### Request Body
None

#### Response
Confirmation that the token has been invalidated.

---

### Me

**Method:** `GET`  
**Endpoint:** `/api/v1/auth/me`  
**Authentication:** Required

#### Description
Return the authenticated user's profile.

#### Headers
```
Accept: application/json
Authorization: Bearer {auth_token}
```

#### Request Body
None

#### Response
Returns the authenticated user's profile information including:
- User ID
- Name
- Email
- Other user details

---

### Forgot Password

**Method:** `POST`  
**Endpoint:** `/api/v1/auth/forgot-password`  
**Authentication:** Not required

#### Description
Send a password reset email to the user.

#### Headers
```
Content-Type: application/json
Accept: application/json
```

#### Request Body
```json
{
  "email": "john@example.com"
}
```

#### Request Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| email | string | Yes | User's email address to send reset link |

#### Response
Confirmation that the password reset email has been sent.

---

### Verify Reset Token

**Method:** `POST`  
**Endpoint:** `/api/v1/auth/reset-token`  
**Authentication:** Not required

#### Description
Validate a reset token before allowing the user to choose a new password.

#### Headers
```
Content-Type: application/json
Accept: application/json
```

#### Request Body
```json
{
  "email": "john@example.com",
  "token": "RESET_TOKEN_FROM_EMAIL"
}
```

#### Request Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| email | string | Yes | User's email address |
| token | string | Yes | Reset token received from the password reset email |

#### Response
Confirmation whether the token is valid or not.

---

### Reset Password

**Method:** `POST`  
**Endpoint:** `/api/v1/auth/reset-password`  
**Authentication:** Not required

#### Description
Reset the password using the emailed token.

#### Headers
```
Content-Type: application/json
Accept: application/json
```

#### Request Body
```json
{
  "email": "john@example.com",
  "token": "RESET_TOKEN_FROM_EMAIL",
  "password": "new-password",
  "password_confirmation": "new-password"
}
```

#### Request Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| email | string | Yes | User's email address |
| token | string | Yes | Reset token received from the password reset email |
| password | string | Yes | New password |
| password_confirmation | string | Yes | Password confirmation (must match password) |

#### Response
Confirmation that the password has been reset successfully.

---

## CVs

The **CVs** folder contains all CV (Curriculum Vitae) management endpoints. This includes creating, reading, updating, deleting CVs, and generating PDFs. The endpoints support both authenticated and unauthenticated access, with different behaviors based on authentication status.

**Folder Description:** CV/Resume management endpoints for creating, managing, and generating PDFs of CVs.

### Create CV (Full Example)

**Method:** `POST`  
**Endpoint:** `/api/v1/cvs`  
**Authentication:** Optional

#### Description
Public endpoint with complete example data. Auth token is optional; provide `user_id` if you want to associate an existing user. This example includes all possible fields: personal info, skills, education, experience, projects, languages, and interests.

**IMPORTANT:** If you are unauthenticated and provide `template_id`, this endpoint will return a PDF file instead of creating a profile. To create a profile AND get PDF, use authenticated requests.

#### Headers
```
Content-Type: application/json
Accept: application/json
```

#### Request Body
Complete example with all available fields:

```json
{
  "name": "My Professional CV",
  "language": "en",
  "user_id": null,
  "template_id": null,
  "sections_order": [
    "Personal Information",
    "Skills",
    "Education",
    "Experience",
    "Projects",
    "Languages",
    "Interests"
  ],
  "user_data": {
    "firstName": "John",
    "lastName": "Doe",
    "jobTitle": "Senior Flutter Developer",
    "email": "john.doe@example.com",
    "address": "123 Tech Street, San Francisco, CA 94105, USA",
    "portfolioUrl": "https://johndoe.dev",
    "phone": "+1-555-123-4567",
    "summary": "Experienced Flutter developer with 5+ years of expertise...",
    "birthdate": "1990-05-15",
    "skills": [
      {"name": "Flutter"},
      {"name": "Dart"},
      {"name": "Laravel"}
    ],
    "educations": [
      {
        "institution": "University of California, Berkeley",
        "degree": "Bachelor of Science",
        "fieldOfStudy": "Computer Science",
        "description": "Graduated magna cum laude...",
        "from": "2010-09",
        "to": "2014-06"
      }
    ],
    "experiences": [
      {
        "position": "Senior Flutter Developer",
        "company": "Tech Innovations Inc.",
        "location": "San Francisco, CA",
        "description": "Lead development of enterprise mobile applications...",
        "from": "2021-03",
        "to": null,
        "current": true
      }
    ],
    "projects": [
      {
        "title": "E-Commerce Mobile App",
        "description": "A full-featured e-commerce mobile application...",
        "technologies": "Flutter, Dart, Firebase, Stripe API, Provider, SQLite",
        "url": "https://github.com/johndoe/ecommerce-app",
        "from": "2022-01",
        "to": "2022-12",
        "current": false
      }
    ],
    "languages": [
      {
        "name": "English",
        "proficiencyLevel": 5
      },
      {
        "name": "Spanish",
        "proficiencyLevel": 3
      }
    ],
    "interests": [
      {"name": "Open Source Contributions"},
      {"name": "Mobile UI/UX Design"}
    ]
  }
}
```

#### Request Parameters

**Top-level fields:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| name | string | Yes | Name/title of the CV |
| language | string | Yes | Language code (e.g., "en", "ar") |
| user_id | integer | No | Associate CV with existing user (if authenticated) |
| template_id | integer | No | Template ID to use (if provided and unauthenticated, returns PDF) |
| sections_order | array | No | Array of section names in desired order |

**user_data object fields:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| firstName | string | Yes | First name |
| lastName | string | Yes | Last name |
| jobTitle | string | No | Current job title |
| email | string | No | Email address |
| address | string | No | Physical address |
| portfolioUrl | string | No | Portfolio website URL |
| phone | string | No | Phone number |
| summary | string | No | Professional summary |
| birthdate | string | No | Birthdate in YYYY-MM-DD format |
| skills | array | No | Array of skill objects: `[{"name": "Skill Name"}]` |
| educations | array | No | Array of education objects |
| experiences | array | No | Array of experience objects |
| projects | array | No | Array of project objects |
| languages | array | No | Array of language objects with proficiency level |
| interests | array | No | Array of interest objects: `[{"name": "Interest Name"}]` |

**Education object:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| institution | string | Yes | School/university name |
| degree | string | Yes | Degree type |
| fieldOfStudy | string | No | Field of study |
| description | string | No | Additional details |
| from | string | No | Start date (YYYY-MM format) |
| to | string | No | End date (YYYY-MM format) |

**Experience object:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| position | string | Yes | Job position title |
| company | string | Yes | Company name |
| location | string | No | Job location |
| description | string | No | Job description |
| from | string | Yes | Start date (YYYY-MM format) |
| to | string | No | End date (YYYY-MM format, null if current) |
| current | boolean | No | Whether currently working here |

**Project object:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| title | string | Yes | Project title |
| description | string | No | Project description |
| technologies | string | No | Technologies used (comma-separated) |
| url | string | No | Project URL |
| from | string | No | Start date (YYYY-MM format) |
| to | string | No | End date (YYYY-MM format) |
| current | boolean | No | Whether currently working on this |

**Language object:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| name | string | Yes | Language name |
| proficiencyLevel | integer | No | Proficiency level (1-5) |

#### Response
- **Authenticated:** Returns created CV profile data
- **Unauthenticated with template_id:** Returns PDF file
- **Unauthenticated without template_id:** Returns created CV profile data

---

### Create CV (Returns PDF - Unauthenticated)

**Method:** `POST`  
**Endpoint:** `/api/v1/cvs`  
**Authentication:** Not required

#### Description
For unauthenticated users: Provides template_id to get PDF directly without creating a profile. Note: Set Accept header to 'application/pdf' to receive PDF response.

#### Headers
```
Content-Type: application/json
Accept: application/pdf
```

#### Request Body
```json
{
  "name": "My CV",
  "language": "en",
  "template_id": 1,
  "user_data": {
    "firstName": "John",
    "lastName": "Doe",
    "jobTitle": "Senior Developer",
    "email": "john@example.com",
    "phone": "+1-555-1234",
    "summary": "Experienced developer with 5+ years in software development.",
    "skills": [
      {"name": "Laravel"},
      {"name": "PHP"},
      {"name": "JavaScript"}
    ],
    "experiences": [
      {
        "position": "Senior Developer",
        "company": "Tech Corp",
        "from": "2021-01",
        "current": true
      }
    ]
  }
}
```

#### Request Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| name | string | Yes | Name/title of the CV |
| language | string | Yes | Language code |
| template_id | integer | Yes | Template ID to use for PDF generation |
| user_data | object | Yes | CV data (see Create CV Full Example for structure) |

#### Response
Returns a PDF file directly without creating a profile in the database.

---

### Create CV (Minimal)

**Method:** `POST`  
**Endpoint:** `/api/v1/cvs`  
**Authentication:** Optional

#### Description
Smallest payload allowed. Creates a profile without user_data.

#### Headers
```
Content-Type: application/json
Accept: application/json
```

#### Request Body
```json
{
  "name": "Minimal CV",
  "language": "en"
}
```

#### Request Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| name | string | Yes | Name/title of the CV |
| language | string | Yes | Language code (e.g., "en", "ar") |

#### Response
Returns created CV profile with minimal data.

---

### Get My CVs

**Method:** `GET`  
**Endpoint:** `/api/v1/cvs`  
**Authentication:** Required

#### Description
Returns ONLY the authenticated user's CVs.

#### Headers
```
Accept: application/json
Authorization: Bearer {auth_token}
```

#### Query Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| language | string | No | Optional filter by language code (e.g., "en") |

#### Example Request
```
GET /api/v1/cvs?language=en
```

#### Response
Returns an array of CV profiles belonging to the authenticated user.

---

### Get CV by ID

**Method:** `GET`  
**Endpoint:** `/api/v1/cvs/:id`  
**Authentication:** Required

#### Description
Fetch one CV owned by the authenticated user.

#### Headers
```
Accept: application/json
Authorization: Bearer {auth_token}
```

#### Path Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| id | integer | Yes | CV profile ID |

#### Example Request
```
GET /api/v1/cvs/1
```

#### Response
Returns the CV profile data for the specified ID if it belongs to the authenticated user.

---

### Update CV

**Method:** `PUT`  
**Endpoint:** `/api/v1/cvs/:id`  
**Authentication:** Required

#### Description
Update fields on a CV you own.

#### Headers
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer {auth_token}
```

#### Path Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| id | integer | Yes | CV profile ID to update |

#### Request Body
```json
{
  "name": "Updated CV Name",
  "language": "ar",
  "user_data": {
    "firstName": "John",
    "lastName": "Doe Updated",
    "jobTitle": "Senior Flutter Dev"
  }
}
```

#### Request Parameters
All fields are optional. Only include the fields you want to update:
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| name | string | No | Updated CV name |
| language | string | No | Updated language code |
| user_data | object | No | Updated user data (see Create CV Full Example for structure) |
| sections_order | array | No | Updated sections order |
| template_id | integer | No | Updated template ID |

#### Response
Returns the updated CV profile data.

---

### Delete CV

**Method:** `DELETE`  
**Endpoint:** `/api/v1/cvs/:id`  
**Authentication:** Required

#### Description
Soft delete a CV you own.

#### Headers
```
Accept: application/json
Authorization: Bearer {auth_token}
```

#### Path Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| id | integer | Yes | CV profile ID to delete |

#### Example Request
```
DELETE /api/v1/cvs/1
```

#### Request Body
None

#### Response
Returns confirmation of deletion.

---

### Print CV

**Method:** `POST`  
**Endpoint:** `/api/v1/cvs/print`  
**Authentication:** Not required (Public endpoint)

#### Description
Generate PDF from CV data. Public endpoint. You can either provide profile_id (for existing profile) or user_data (to create temporary CV). Template ID is required. Response will be a PDF file.

#### Headers
```
Content-Type: application/json
Accept: application/pdf
```

#### Request Body
**Option 1: Using user_data (temporary CV)**
```json
{
  "template_id": 1,
  "user_data": {
    "firstName": "John",
    "lastName": "Doe",
    "jobTitle": "Senior Flutter Developer",
    "email": "john.doe@example.com",
    "address": "123 Tech Street, San Francisco, CA 94105, USA",
    "portfolioUrl": "https://johndoe.dev",
    "phone": "+1-555-123-4567",
    "summary": "Experienced Flutter developer...",
    "birthdate": "1990-05-15",
    "skills": [
      {"name": "Flutter"},
      {"name": "Dart"}
    ],
    "educations": [
      {
        "institution": "University of California, Berkeley",
        "degree": "Bachelor of Science",
        "fieldOfStudy": "Computer Science",
        "description": "Graduated magna cum laude...",
        "from": "2010-09",
        "to": "2014-06"
      }
    ],
    "experiences": [
      {
        "position": "Senior Flutter Developer",
        "company": "Tech Innovations Inc.",
        "location": "San Francisco, CA",
        "description": "Lead development of enterprise mobile applications...",
        "from": "2021-03",
        "to": null,
        "current": true
      }
    ],
    "projects": [
      {
        "title": "E-Commerce Mobile App",
        "description": "A full-featured e-commerce mobile application...",
        "technologies": "Flutter, Dart, Firebase",
        "url": "https://github.com/johndoe/ecommerce-app",
        "from": "2022-01",
        "to": "2022-12",
        "current": false
      }
    ],
    "languages": [
      {"name": "English", "proficiencyLevel": 5},
      {"name": "Spanish", "proficiencyLevel": 3}
    ],
    "interests": [
      {"name": "Open Source"},
      {"name": "Mobile UI/UX Design"}
    ]
  }
}
```

#### Request Parameters

**Option 1: Using user_data**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| template_id | integer | Yes | Template ID to use for PDF generation |
| user_data | object | Yes | CV data (see Create CV Full Example for structure) |

**Option 2: Using profile_id**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| template_id | integer | Yes | Template ID to use for PDF generation |
| profile_id | integer | Yes | Existing CV profile ID |

#### Response
Returns a PDF file of the CV.

---

### Print CV (From Profile ID)

**Method:** `POST`  
**Endpoint:** `/api/v1/cvs/print`  
**Authentication:** Required (if profile belongs to a user)

#### Description
Generate PDF from an existing profile. Requires authentication if profile belongs to a user. Provide profile_id and template_id.

#### Headers
```
Content-Type: application/json
Accept: application/pdf
Authorization: Bearer {auth_token} (if profile belongs to a user)
```

#### Request Body
```json
{
  "profile_id": 1,
  "template_id": 1
}
```

#### Request Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| profile_id | integer | Yes | Existing CV profile ID |
| template_id | integer | Yes | Template ID to use for PDF generation |

#### Response
Returns a PDF file of the CV using the specified profile and template.

---

## Shares

The **Shares** folder contains public endpoints that don't require authentication. These endpoints provide access to shared resources like templates that can be used by anyone.

**Folder Description:** Public shared resources endpoints.

### Get Templates

**Method:** `GET`  
**Endpoint:** `/api/v1/shares/templates`  
**Authentication:** Not required (Public endpoint)

#### Description
Public endpoint to retrieve all active templates. No authentication required.

#### Headers
```
Accept: application/json
```

#### Request Body
None

#### Response
Returns an array of all active CV templates with details such as:
- Template ID
- Template name
- Preview URL
- Description
- Other template metadata

---

## Notes

### Authentication Behavior

- **Unauthenticated requests:** Can create CVs, generate PDFs, and access public templates
- **Authenticated requests:** Full CRUD operations, can manage owned CVs, and have persistent profiles
- **Token storage:** Tokens are automatically managed via Postman collection scripts

### PDF Generation

- Set `Accept: application/pdf` header to receive PDF responses
- For unauthenticated users with `template_id`, the Create CV endpoint returns PDF directly
- The Print CV endpoint always returns PDF regardless of authentication status

### Date Formats

- **Birthdate:** `YYYY-MM-DD` (e.g., "1990-05-15")
- **Education/Experience/Project dates:** `YYYY-MM` (e.g., "2021-03")
- Use `null` for ongoing/current items where `to` date applies

### Language Support

Supported language codes include:
- `en` - English
- `ar` - Arabic
- Other languages as configured

---

## Error Responses

All endpoints follow a consistent error response format:

```json
{
  "error": {
    "code": "error_code",
    "message": "Human-readable error message",
    "fields": {
      "field_name": "Field-specific error message"
    }
  }
}
```

Common HTTP status codes:
- `200` - Success
- `201` - Created
- `400` - Bad Request (validation errors)
- `401` - Unauthorized (authentication required)
- `403` - Forbidden (insufficient permissions)
- `404` - Not Found
- `422` - Unprocessable Entity (validation failed)
- `500` - Internal Server Error

---

## Support

For issues or questions about the API, please refer to the main project documentation or contact the development team.

**Last Updated:** Based on Postman Collection version dated at collection creation

