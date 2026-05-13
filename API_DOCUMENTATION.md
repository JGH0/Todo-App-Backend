# Todo App API Documentation

## Version: 1.0

Base URL: `http://localhost:8080/api/v1`

## Overview

This API provides access to the Todo App functionality with versioned endpoints. The API uses API key authentication for protected endpoints, while some endpoints (like the marketplace) are publicly accessible.

## Authentication

### API Key Authentication

Most endpoints require an API key for authentication. The API key should be included in the `X-API-Key` header.

**Header:**
```
X-API-Key: todo_your_api_key_here
```

### Register a New User

**Endpoint:** `POST /api/v1/auth/register`

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "your_password",
  "name": "John Doe",
  "avatar_url": "https://example.com/avatar.jpg",
  "settings": {
    "theme": "dark",
    "language": "en"
  }
}
```

**Response:**
```json
{
  "success": true,
  "message": "User registered successfully",
  "data": {
    "user": {
      "id": "user-uuid",
      "email": "user@example.com",
      "name": "John Doe",
      "avatar_url": "https://example.com/avatar.jpg",
      "settings": {"theme": "dark"},
      "created_at": "2025-01-01 00:00:00",
      "updated_at": "2025-01-01 00:00:00"
    },
    "api_key": "todo_abc123...",
    "key_prefix": "todo_abc1"
  }
}
```

**Important:** Store the API key securely. You won't be able to retrieve it again.

### Login

**Endpoint:** `POST /api/v1/auth/login`

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "your_password"
}
```

**Response (New API Key Created):**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": "user-uuid",
      "email": "user@example.com",
      "name": "John Doe"
    },
    "api_key": "todo_abc123...",
    "key_prefix": "todo_abc1"
  }
}
```

**Response (Using Existing API Key):**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": "user-uuid",
      "email": "user@example.com",
      "name": "John Doe"
    },
    "api_key_prefix": "todo_abc1",
    "message": "Using existing API key"
  }
}
```

**Note:** If you already have an active API key, the login will return the key prefix only (not the full key for security). You should store your API key securely after the first login.

### Creating an API Key (Legacy)

To create an additional API key, you can use this endpoint:

**Endpoint:** `POST /api/v1/auth/api-key`

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "your_password",
  "name": "My App Key",
  "scopes": ["read", "write"],
  "expires_at": "2026-12-31 23:59:59"
}
```

**Response:**
```json
{
  "success": true,
  "message": "API key created successfully",
  "data": {
    "key": "todo_abc123...",
    "prefix": "todo_abc1",
    "name": "My App Key",
    "scopes": ["read", "write"],
    "expires_at": "2026-12-31 23:59:59"
  }
}
```

### Scopes

API keys can have the following scopes:
- `read` - Read-only access to data
- `write` - Full access to create, update, and delete data

If no scopes are specified, the key will have full access.

## Public Endpoints

These endpoints do not require authentication.

### Marketplace Themes

#### Get All Themes
**Endpoint:** `GET /api/v1/marketplace/themes`

**Response:**
```json
{
  "success": true,
  "message": "Marketplace themes retrieved successfully",
  "data": [
    {
      "id": "theme-id-1",
      "name": "Dark Theme",
      "description": "A dark theme for the app",
      "preview_url": "https://example.com/preview.png",
      "price": 0,
      "is_free": true,
      "created_at": "2025-01-01 00:00:00"
    }
  ]
}
```

#### Get Theme by ID
**Endpoint:** `GET /api/v1/marketplace/themes/{id}`

**Response:**
```json
{
  "success": true,
  "message": "Theme retrieved successfully",
  "data": {
    "id": "theme-id-1",
    "name": "Dark Theme",
    "description": "A dark theme for the app",
    "preview_url": "https://example.com/preview.png",
    "price": 0,
    "is_free": true,
    "created_at": "2025-01-01 00:00:00"
  }
}
```

## Protected Endpoints

These endpoints require an API key in the `X-API-Key` header.

### User Management

#### Get User Profile
**Endpoint:** `GET /api/v1/user/profile`

**Response:**
```json
{
  "success": true,
  "message": "Profile retrieved successfully",
  "data": {
    "id": "user-id",
    "email": "user@example.com",
    "name": "John Doe",
    "avatar_url": null,
    "settings": {"theme": "dark"},
    "created_at": "2025-01-01 00:00:00",
    "updated_at": "2025-01-01 00:00:00"
  }
}
```

#### Update User Profile
**Endpoint:** `PUT /api/v1/user/profile`

**Request Body:**
```json
{
  "name": "Jane Doe",
  "avatar_url": "https://example.com/avatar.jpg",
  "settings": {"theme": "light", "language": "en"}
}
```

#### List API Keys
**Endpoint:** `GET /api/v1/user/api-keys`

**Response:**
```json
{
  "success": true,
  "message": "API keys retrieved successfully",
  "data": [
    {
      "id": "key-id",
      "key_prefix": "todo_abc1",
      "name": "My App Key",
      "scopes": ["read", "write"],
      "is_active": true,
      "last_used_at": "2025-01-01 12:00:00",
      "created_at": "2025-01-01 00:00:00"
    }
  ]
}
```

#### Create API Key
**Endpoint:** `POST /api/v1/user/api-keys`

**Request Body:**
```json
{
  "name": "New App Key",
  "scopes": ["read"],
  "expires_at": "2026-12-31 23:59:59"
}
```

#### Revoke API Key
**Endpoint:** `DELETE /api/v1/user/api-keys/{id}`

### Categories

#### Get All Categories
**Endpoint:** `GET /api/v1/categories`

**Response:**
```json
{
  "success": true,
  "message": "Categories retrieved successfully",
  "data": [
    {
      "id": "cat-id-1",
      "user_id": "user-id",
      "name": "Work",
      "color": "#3B82F6",
      "favorite": true,
      "created_at": "2025-01-01 00:00:00"
    }
  ]
}
```

#### Create Category
**Endpoint:** `POST /api/v1/categories`

**Request Body:**
```json
{
  "name": "Personal",
  "color": "#10B981",
  "favorite": false
}
```

#### Get Category
**Endpoint:** `GET /api/v1/categories/{id}`

#### Update Category
**Endpoint:** `PUT /api/v1/categories/{id}`

**Request Body:**
```json
{
  "name": "Updated Name",
  "color": "#FF5733",
  "favorite": true
}
```

#### Delete Category
**Endpoint:** `DELETE /api/v1/categories/{id}`

### Projects

#### Get All Projects
**Endpoint:** `GET /api/v1/projects`

**Response:**
```json
{
  "success": true,
  "message": "Projects retrieved successfully",
  "data": [
    {
      "id": "proj-id-1",
      "user_id": "user-id",
      "name": "Web Redesign",
      "description": "Redesign the company website",
      "color": "#8B5CF6",
      "created_at": "2025-01-01 00:00:00"
    }
  ]
}
```

#### Create Project
**Endpoint:** `POST /api/v1/projects`

**Request Body:**
```json
{
  "name": "New Project",
  "description": "Project description",
  "color": "#EC4899"
}
```

#### Get Project
**Endpoint:** `GET /api/v1/projects/{id}`

#### Update Project
**Endpoint:** `PUT /api/v1/projects/{id}`

**Request Body:**
```json
{
  "name": "Updated Project",
  "description": "Updated description",
  "color": "#14B8A6"
}
```

#### Delete Project
**Endpoint:** `DELETE /api/v1/projects/{id}`

### Todos

#### Get All Todos
**Endpoint:** `GET /api/v1/todos`

**Response:**
```json
{
  "success": true,
  "message": "Todos retrieved successfully",
  "data": [
    {
      "id": "todo-id-1",
      "user_id": "user-id",
      "title": "Complete task",
      "description": "Task description",
      "status": "open",
      "due_date": "2025-01-15",
      "due_time": "10:30:00",
      "sync_enabled": true,
      "reminder_enabled": false,
      "recurring_enabled": false,
      "project_id": "proj-id-1",
      "created_at": "2025-01-01 00:00:00",
      "updated_at": "2025-01-01 00:00:00",
      "categories": [
        {
          "id": "cat-id-1",
          "name": "Work",
          "color": "#3B82F6"
        }
      ]
    }
  ]
}
```

#### Create Todo
**Endpoint:** `POST /api/v1/todos`

**Request Body:**
```json
{
  "title": "New Task",
  "description": "Task description",
  "status": "open",
  "due_date": "2025-01-15",
  "due_time": "10:30:00",
  "sync_enabled": true,
  "reminder_enabled": false,
  "recurring_enabled": false,
  "project_id": "proj-id-1"
}
```

**Status options:** `open`, `in_progress`, `completed`, `archived`

#### Get Todo
**Endpoint:** `GET /api/v1/todos/{id}`

#### Update Todo
**Endpoint:** `PUT /api/v1/todos/{id}`

**Request Body:**
```json
{
  "title": "Updated Task",
  "status": "in_progress",
  "due_date": "2025-01-20"
}
```

#### Delete Todo
**Endpoint:** `DELETE /api/v1/todos/{id}`

#### Add Category to Todo
**Endpoint:** `POST /api/v1/todos/{id}/categories`

**Request Body:**
```json
{
  "category_id": "cat-id-1"
}
```

#### Remove Category from Todo
**Endpoint:** `DELETE /api/v1/todos/{id}/categories/{categoryId}`

### Recurring Tasks

#### Get All Recurring Tasks
**Endpoint:** `GET /api/v1/recurring-tasks`

**Response:**
```json
{
  "success": true,
  "message": "Recurring tasks retrieved successfully",
  "data": [
    {
      "id": "rt-id-1",
      "user_id": "user-id",
      "title": "Weekly Review",
      "description": "Plan next week's tasks",
      "schedule": "weekly",
      "custom_days": [],
      "favorite": true,
      "created_at": "2025-01-01 00:00:00",
      "updated_at": "2025-01-01 00:00:00",
      "categories": [
        {
          "id": "cat-id-1",
          "name": "Work",
          "color": "#3B82F6"
        }
      ]
    }
  ]
}
```

#### Create Recurring Task
**Endpoint:** `POST /api/v1/recurring-tasks`

**Request Body:**
```json
{
  "title": "Daily Standup",
  "description": "Team meeting every morning",
  "schedule": "daily",
  "custom_days": [],
  "favorite": true
}
```

**Schedule options:** `daily`, `weekly`, `monthly`, `custom`

For `custom` schedule, provide days in `custom_days` array: `["mon", "wed", "fri"]`

#### Get Recurring Task
**Endpoint:** `GET /api/v1/recurring-tasks/{id}`

#### Update Recurring Task
**Endpoint:** `PUT /api/v1/recurring-tasks/{id}`

**Request Body:**
```json
{
  "title": "Updated Task",
  "schedule": "weekly",
  "custom_days": ["mon"]
}
```

#### Delete Recurring Task
**Endpoint:** `DELETE /api/v1/recurring-tasks/{id}`

#### Add Category to Recurring Task
**Endpoint:** `POST /api/v1/recurring-tasks/{id}/categories`

**Request Body:**
```json
{
  "category_id": "cat-id-1"
}
```

#### Remove Category from Recurring Task
**Endpoint:** `DELETE /api/v1/recurring-tasks/{id}/categories/{categoryId}`

### Activity Logs

#### Get Activity Logs
**Endpoint:** `GET /api/v1/activity-logs?limit=50`

**Response:**
```json
{
  "success": true,
  "message": "Activity logs retrieved successfully",
  "data": [
    {
      "id": "log-id-1",
      "user_id": "user-id",
      "action": "todo_created",
      "entity_type": "todo",
      "entity_id": "todo-id-1",
      "details": {
        "title": "New Task"
      },
      "ip_address": "127.0.0.1",
      "user_agent": "Mozilla/5.0...",
      "created_at": "2025-01-01 12:00:00"
    }
  ]
}
```

#### Get Activity Log
**Endpoint:** `GET /api/v1/activity-logs/{id}`

### User Themes

#### Get User Themes
**Endpoint:** `GET /api/v1/user/themes`

**Response:**
```json
{
  "success": true,
  "message": "User themes retrieved successfully",
  "data": [
    {
      "id": "ut-id-1",
      "user_id": "user-id",
      "theme_id": "theme-id-1",
      "is_active": true,
      "custom_settings": {"primary_color": "#3B82F6"},
      "created_at": "2025-01-01 00:00:00"
    }
  ]
}
```

#### Create User Theme
**Endpoint:** `POST /api/v1/user/themes`

**Request Body:**
```json
{
  "theme_id": "theme-id-1",
  "is_active": true,
  "custom_settings": {
    "primary_color": "#3B82F6",
    "font_size": "medium"
  }
}
```

#### Update User Theme
**Endpoint:** `PUT /api/v1/user/themes/{id}`

**Request Body:**
```json
{
  "is_active": false,
  "custom_settings": {
    "primary_color": "#EC4899"
  }
}
```

#### Delete User Theme
**Endpoint:** `DELETE /api/v1/user/themes/{id}`

## Error Responses

All error responses follow this format:

```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    "field": "Validation error message"
  }
}
```

### Common HTTP Status Codes

- `200 OK` - Request successful
- `201 Created` - Resource created successfully
- `400 Bad Request` - Invalid request data
- `401 Unauthorized` - Missing or invalid API key
- `403 Forbidden` - Insufficient permissions
- `404 Not Found` - Resource not found
- `409 Conflict` - Resource already exists
- `422 Unprocessable Entity` - Validation failed
- `500 Internal Server Error` - Server error

## Rate Limiting

Currently, there is no rate limiting implemented. Consider adding rate limiting for production use.

## CORS

If you need to enable CORS for frontend applications, configure it in `app/Config/Filters.php`.

## Example Usage

### cURL Examples

**Register a New User:**
```bash
curl -X POST http://localhost:8080/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "email": "newuser@example.com",
    "password": "securepassword",
    "name": "New User"
  }'
```

**Login:**
```bash
curl -X POST http://localhost:8080/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "password": "password123"
  }'
```

**Create API Key (additional key):**
```bash
curl -X POST http://localhost:8080/api/v1/auth/api-key \
  -H "Content-Type: application/json" \
  -d '{
    "email": "demo@example.com",
    "password": "password123",
    "name": "My App Key"
  }'
```

**Get Todos (with API key):**
```bash
curl -X GET http://localhost:8080/api/v1/todos \
  -H "X-API-Key: todo_your_api_key_here"
```

**Create Todo:**
```bash
curl -X POST http://localhost:8080/api/v1/todos \
  -H "X-API-Key: todo_your_api_key_here" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "New Task",
    "status": "open"
  }'
```

### JavaScript/Fetch Examples

**Register a New User:**
```javascript
fetch('http://localhost:8080/api/v1/auth/register', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    email: 'newuser@example.com',
    password: 'securepassword',
    name: 'New User'
  })
})
.then(response => response.json())
.then(data => {
  console.log('API Key:', data.data.api_key);
  console.log('User:', data.data.user);
});
```

**Login:**
```javascript
fetch('http://localhost:8080/api/v1/auth/login', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    email: 'user@example.com',
    password: 'password123'
  })
})
.then(response => response.json())
.then(data => {
  if (data.data.api_key) {
    console.log('New API Key:', data.data.api_key);
  } else {
    console.log('Using existing key with prefix:', data.data.api_key_prefix);
  }
});
```

**Create API Key (additional key):**
```javascript
fetch('http://localhost:8080/api/v1/auth/api-key', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    email: 'demo@example.com',
    password: 'password123',
    name: 'My App Key'
  })
})
.then(response => response.json())
.then(data => console.log(data));
```

**Get Todos:**
```javascript
fetch('http://localhost:8080/api/v1/todos', {
  headers: {
    'X-API-Key': 'todo_your_api_key_here'
  }
})
.then(response => response.json())
.then(data => console.log(data));
```

**Create Todo:**
```javascript
fetch('http://localhost:8080/api/v1/todos', {
  method: 'POST',
  headers: {
    'X-API-Key': 'todo_your_api_key_here',
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    title: 'New Task',
    status: 'open'
  })
})
.then(response => response.json())
.then(data => console.log(data));
```

## Testing

To test the API, you can use tools like:
- Postman
- Insomnia
- cURL
- HTTPie

## Versioning

The API is versioned using the URL path. The current version is `v1`. Future versions will be numbered incrementally (v2, v3, etc.).

## Support

For issues or questions, please refer to the project documentation or contact the development team.
