# Todo App Backend

A RESTful API backend for a todo application built with **CodeIgniter 4**.
Supports user authentication (API key + JWT), CRUD for todos/categories/projects,
recurring tasks, activity logging, and a theme marketplace.

---

## Table of Contents

- [Quick Start](#quick-start)
- [API Documentation](#api-documentation)
- [Authentication](#authentication)
- [API Overview](#api-overview)
- [Testing](#testing)
- [Project Structure](#project-structure)
- [Database](#database)
- [Development](#development)
- [Contributing](#contributing)

---

## Quick Start

### Requirements

- PHP ^8.2
- MySQL 8+ (or MariaDB 10.5+)
- Composer
- `ext-intl`, `ext-mbstring`

### Setup

```bash
# 1. Clone and enter the project
cd Todo-App-Backend

# 2. Install dependencies
composer install

# 3. Configure your environment
cp env.example .env
# Edit .env — set database credentials and app.baseURL

# 4. Run database migrations
php spark migrate

# 5. (Optional) Seed sample data
php spark db:seed SampleDataSeeder

# 6. Start the development server
php spark serve

# The API is now available at http://localhost:8080/api/v1
```

### Quick Test

```bash
# Register a new user
curl -s -X POST http://localhost:8080/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{"email":"demo@example.com","password":"password123","name":"Demo User"}'

# Save the returned api_key, then:
curl -s http://localhost:8080/api/v1/todos \
  -H "X-API-Key: todo_your_key_here"
```

---

## API Documentation

The API is fully documented using the **OpenAPI 3.0** specification.

| Resource | Location |
|----------|----------|
| OpenAPI spec (canonical) | [`openapi/openapi.yaml`](openapi/openapi.yaml) |
| Generated HTML docs | `public/api-docs.html` (generated, see below) |
| Swagger/Postman import | Use `openapi/openapi.yaml` directly |

### Generating API Docs

From the project root:

```bash
# Generate HTML documentation page
php spark generate:api-docs

# Validate spec only (no file written)
php spark generate:api-docs --watch

# Open http://localhost:8080/api-docs.html after generating
```

The generated HTML uses [Redoc](https://redocly.com/redoc) for rendering and is
fully self-contained (the spec is embedded as a base64 data URI).

### Importing into Tools

- **Postman**: File → Import → choose `openapi/openapi.yaml`
- **Insomnia**: Import → From File → choose `openapi/openapi.yaml`
- **Swagger Editor**: Paste the contents of `openapi/openapi.yaml`
- **cURL/HTTPie**: Examples are in the OpenAPI spec under each endpoint

---

## Authentication

The API supports two authentication methods:

### 1. API Key Authentication (Primary)

```
X-API-Key: todo_abc123def456...
```

Used by most protected endpoints. Keys are obtained on registration or can be
created via `POST /user/api-keys`. Keys can be scoped (`read`, `write`) and
optionally expire.

### 2. JWT Bearer Authentication

```
Authorization: Bearer eyJ0eXAiOiJKV1Qi...
```

Available via the `/auth/jwt/*` endpoints. Tokens are valid for 1 hour and can
be refreshed via `/auth/jwt/refresh`.

### Authentication Flow

1. **Register** → receive API key + key prefix
2. **Login** → receive the same or existing API key
3. Include `X-API-Key` header on all protected requests
4. Optionally use JWT endpoints for short-lived bearer tokens

---

## API Overview

All endpoints live under the `/api/v1` prefix.

### Public Endpoints

| Method | Path | Description |
|--------|------|-------------|
| POST | `/auth/register` | Register a new user |
| POST | `/auth/login` | Login and get API key |
| POST | `/auth/api-key` | Create additional API key (legacy) |
| POST | `/auth/jwt/register` | Register and receive JWT + API key |
| POST | `/auth/jwt/login` | Login and receive JWT |
| POST | `/auth/jwt/refresh` | Refresh an existing JWT |
| GET | `/marketplace/themes` | List published themes |
| GET | `/marketplace/themes/{id}` | Get a single theme |

### Protected Endpoints (API key required)

#### User

| Method | Path | Description |
|--------|------|-------------|
| GET | `/user/profile` | Get your profile |
| PUT | `/user/profile` | Update your profile |
| GET | `/user/api-keys` | List your API keys |
| POST | `/user/api-keys` | Create a new API key |
| DELETE | `/user/api-keys/{id}` | Revoke an API key |

#### Categories

| Method | Path | Description |
|--------|------|-------------|
| GET | `/categories` | List categories (paginated, sortable) |
| POST | `/categories` | Create a category |
| GET | `/categories/{id}` | Get a category |
| PUT | `/categories/{id}` | Update a category |
| DELETE | `/categories/{id}` | Delete a category |

#### Projects

| Method | Path | Description |
|--------|------|-------------|
| GET | `/projects` | List projects (paginated, sortable) |
| POST | `/projects` | Create a project |
| GET | `/projects/{id}` | Get a project |
| PUT | `/projects/{id}` | Update a project |
| DELETE | `/projects/{id}` | Delete a project |

#### Todos

| Method | Path | Description |
|--------|------|-------------|
| GET | `/todos` | List todos (paginated, sortable, filterable) |
| POST | `/todos` | Create a todo |
| GET | `/todos/{id}` | Get a todo |
| PUT | `/todos/{id}` | Update a todo |
| DELETE | `/todos/{id}` | Delete a todo |
| POST | `/todos/{id}/categories` | Link a category |
| DELETE | `/todos/{id}/categories/{catId}` | Unlink a category |

#### Recurring Tasks

| Method | Path | Description |
|--------|------|-------------|
| GET | `/recurring-tasks` | List recurring tasks (paginated, sortable, filterable) |
| POST | `/recurring-tasks` | Create a recurring task |
| GET | `/recurring-tasks/{id}` | Get a recurring task |
| PUT | `/recurring-tasks/{id}` | Update a recurring task |
| DELETE | `/recurring-tasks/{id}` | Delete a recurring task |
| POST | `/recurring-tasks/{id}/categories` | Link a category |
| DELETE | `/recurring-tasks/{id}/categories/{catId}` | Unlink a category |

#### Activity Logs

| Method | Path | Description |
|--------|------|-------------|
| GET | `/activity-logs` | List activity logs |
| GET | `/activity-logs/{id}` | Get a single log entry |

#### User Themes

| Method | Path | Description |
|--------|------|-------------|
| GET | `/user/themes` | List installed themes |
| POST | `/user/themes` | Install a theme |
| PUT | `/user/themes/{id}` | Update theme settings |
| DELETE | `/user/themes/{id}` | Uninstall a theme |

### Common Query Parameters

| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `page` | int | Page number (default: 1) | `?page=2` |
| `per_page` | int | Items per page (default: 50, max: 200) | `?per_page=10` |
| `sort` | string | Sort fields, `-` for descending, comma-separated | `?sort=-created_at,title` |
| `status` | string | Filter by status (todos) | `?status=open` |
| `favorite` | bool | Filter favorites (categories) | `?favorite=1` |
| `limit` | int | Max items (activity logs, default: 50) | `?limit=100` |

### Sorting

Sortable fields vary per resource. Prefix a field with `-` for descending:

```
GET /api/v1/todos?sort=-created_at,title
GET /api/v1/categories?sort=name
GET /api/v1/projects?sort=-created_at
GET /api/v1/recurring-tasks?sort=title,-created_at
```

### Response Format

All responses follow a consistent envelope:

**Success:**
```json
{
  "success": true,
  "message": "Todos retrieved successfully",
  "data": [ ... ],
  "pagination": {
    "page": 1,
    "per_page": 50,
    "total": 123,
    "last_page": 3,
    "has_more": true
  }
}
```

**Error:**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "title": "The todo title is required."
  }
}
```

### HTTP Status Codes

| Code | Meaning |
|------|---------|
| 200 | Success |
| 201 | Created |
| 400 | Bad request |
| 401 | Unauthorized (missing/invalid API key) |
| 403 | Forbidden (insufficient scope) |
| 404 | Not found |
| 409 | Conflict (duplicate) |
| 422 | Validation failed |
| 500 | Server error |

### Pagination

Paginated responses include a `pagination` object:

```json
{
  "pagination": {
    "page": 1,
    "per_page": 50,
    "total": 123,
    "last_page": 3,
    "has_more": true
  }
}
```

### Todo Status Values

- `open`
- `in_progress`
- `completed`
- `archived`

### Recurring Task Schedule Values

- `daily`
- `weekly`
- `monthly`
- `custom` (requires `custom_days` array, e.g. `["mon","wed","fri"]`)

---

## Testing

### Running Tests

```bash
# Run all tests via composer
composer run test

# Or use phpunit directly
./vendor/bin/phpunit

# Run only API tests
./vendor/bin/phpunit tests/api

# With code coverage
./vendor/bin/phpunit --coverage-text
```

### Database Setup

Integration tests use your configured MySQL database. Make sure migrations are applied first:

```bash
php spark migrate
```

For a dedicated test database, uncomment the test DB config in `phpunit.xml.dist`:

```xml
<env name="database.tests.hostname" value="localhost"/>
<env name="database.tests.database"   value="todo_app_test"/>
<env name="database.tests.username"   value="root"/>
<env name="database.tests.password"   value=""/>
<env name="database.tests.DBDriver"   value="MySQLi"/>
```

Then create it and migrate:

```bash
mysql -e "CREATE DATABASE IF NOT EXISTS todo_app_test;"
php spark migrate
```

### Test Suite

| Directory | Description |
|-----------|-------------|
| `tests/api/ApiTest.php` | Full API integration tests (auth, CRUD, filtering, error handling) |
| `tests/unit/` | Unit tests for individual components |
| `tests/database/` | Database migration and seed tests |
| `tests/session/` | Session-related tests |

The API test suite covers:

- Registration and login
- Authentication errors (missing key, invalid credentials)
- Full CRUD for all resources (categories, projects, todos, recurring tasks)
- Category-todo / category-recurring-task linking
- Status and sort filtering
- Activity logging verification
- Ownership isolation (cross-user access denied)
- Validation error responses
- Pagination structure

---

## Project Structure

```
├── app/
│   ├── Commands/           # Spark CLI commands
│   │   ├── TestModels.php    # Model testing command
│   │   └── GenerateApiDocs.php  # OpenAPI → HTML docs generator
│   ├── Config/             # Application configuration
│   ├── Controllers/
│   │   ├── Api/
│   │   │   ├── BaseController.php   # Shared API helpers (pagination, JWT, responses)
│   │   │   └── V1/
│   │   │       ├── AuthController.php
│   │   │       ├── CategoryController.php
│   │   │       ├── ProjectController.php
│   │   │       ├── TodoController.php
│   │   │       ├── RecurringTaskController.php
│   │   │       ├── UserController.php
│   │   │       ├── ActivityLogController.php
│   │   │       ├── MarketplaceController.php
│   │   │       └── UserThemeController.php
│   │   ├── BaseController.php
│   │   ├── Home.php
│   │   └── ThemeStore.php
│   ├── Database/
│   │   ├── Migrations/     # 16 migration files (users → api_auth_keys)
│   │   └── Seeds/          # Sample data, themes, AI providers
│   ├── Filters/
│   │   └── ApiAuthFilter.php   # API key authentication filter
│   ├── Models/             # Database models (TodoModel, CategoryModel, etc.)
│   └── Views/              # Error pages, welcome message, theme store
├── openapi/
│   └── openapi.yaml        # Canonical OpenAPI 3.0 specification
├── public/
│   ├── api-docs.html       # Generated API documentation (gitignored?)
│   ├── index.php           # Front controller
│   └── themes/             # Uploaded theme CSS files
├── tests/
│   ├── api/ApiTest.php     # Full API integration tests
│   ├── unit/               # Unit tests
│   ├── database/           # Database tests
│   └── _support/           # Test helpers, models, seeds
├── writable/               # Logs, cache, uploads
├── composer.json
├── env.example
└── README.md
```

---

## Database

### Schema Overview

The database consists of 12 tables:

| Table | Description |
|-------|-------------|
| `users` | User accounts (email, password hash, settings) |
| `api_auth_keys` | API keys (hashed, scoped, expirable) |
| `categories` | User-defined categories (with hex color) |
| `projects` | User-defined projects |
| `todos` | Tasks with status, due dates, project links |
| `todo_categories` | Many-to-many: todos ↔ categories |
| `recurring_tasks` | Recurring task templates (daily/weekly/etc.) |
| `recurring_task_categories` | Many-to-many: recurring_tasks ↔ categories |
| `activity_logs` | Audit trail (CRUD events, login, etc.) |
| `marketplace_themes` | Published theme definitions |
| `user_themes` | Per-user theme installations |
| `ai_chats / ai_messages / ai_providers / user_ai_settings / user_api_keys` | AI assistant features |

### Migrations

```bash
# Run all pending migrations
php spark migrate

# Roll back all migrations
php spark migrate:rollback

# Seed sample data
php spark db:seed SampleDataSeeder
```

---

## Development

### Adding a New Endpoint

1. Add the route in `app/Config/Routes.php`
2. Create the controller method (extends `App\Controllers\Api\BaseController`)
3. Create the model (extends `CodeIgniter\Model`)
4. Write migration if needed
5. Update `openapi/openapi.yaml` with the new endpoint
6. Run `php spark generate:api-docs` to regenerate HTML docs
7. Write tests in `tests/api/ApiTest.php`

### Updating Documentation

The **single source of truth** is `openapi/openapi.yaml`. After any API change:

1. Update the YAML spec
2. Run `php spark generate:api-docs`
3. Commit both files

### Available Spark Commands

```bash
php spark list                     # List all available commands
php spark generate:api-docs        # Generate HTML docs from OpenAPI spec
php spark generate:api-docs --watch  # Validate spec only
php spark migrate                  # Run database migrations
php spark db:seed                  # Seed the database
composer run test                 # Run all tests
php spark test:models               # Test models
```

### Common Workflows

**New todo with category:**
```bash
KEY="todo_your_key_here"

# Create category
CAT=$(curl -s -X POST http://localhost:8080/api/v1/categories \
  -H "X-API-Key: $KEY" \
  -H "Content-Type: application/json" \
  -d '{"name":"Work","color":"#3B82F6"}')
CAT_ID=$(echo $CAT | grep -o '"id":"[^"]*"' | head -1 | cut -d'"' -f4)

# Create todo with that category
curl -s -X POST http://localhost:8080/api/v1/todos \
  -H "X-API-Key: $KEY" \
  -H "Content-Type: application/json" \
  -d "{\"title\":\"Finish report\",\"status\":\"open\",\"category_id\":\"$CAT_ID\"}"
```

**Filter and sort todos:**
```bash
curl -s "http://localhost:8080/api/v1/todos?status=open&sort=-due_date,title&per_page=5" \
  -H "X-API-Key: $KEY"
```

---

## Contributing

1. Keep the OpenAPI spec (`openapi/openapi.yaml`) in sync with code changes
2. Run `php spark generate:api-docs --watch` to validate your YAML changes
3. Write tests for new endpoints
4. Run the full test suite before pushing
5. Follow CodeIgniter 4 conventions

---

## License

MIT — see [LICENSE](LICENSE).
