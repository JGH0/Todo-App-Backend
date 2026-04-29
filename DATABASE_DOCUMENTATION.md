# Todo App Backend - Database Documentation

## Overview

This document describes the database schema for the Todo App Backend, built with CodeIgniter 4 and MySQL. The database supports user accounts, todo management, recurring tasks, activity logging, theme marketplace, and AI-powered chat features.

## Database Schema

### Core Tables

#### 1. users
Stores user account information and application settings.

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | CHAR(36) | NO | Primary key (UUID) |
| email | VARCHAR(255) | NO | User email (unique) |
| password_hash | VARCHAR(255) | NO | Bcrypt hashed password |
| name | VARCHAR(255) | YES | Display name |
| avatar_url | TEXT | YES | Profile image URL |
| settings | JSON | YES | App preferences (language, default view, etc.) |
| created_at | DATETIME | YES | Creation timestamp |
| updated_at | DATETIME | YES | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE KEY (email)

---

#### 2. categories
Per-user categories for organizing todos.

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | CHAR(36) | NO | Primary key (UUID) |
| user_id | CHAR(36) | NO | Foreign key to users |
| name | VARCHAR(255) | NO | Category name |
| color | VARCHAR(7) | YES | Hex color code for UI |
| favorite | BOOLEAN | NO | Mark as favorite |
| created_at | DATETIME | YES | Creation timestamp |

**Indexes:**
- PRIMARY KEY (id)
- KEY (user_id)
- UNIQUE KEY (user_id, name)

**Foreign Keys:**
- user_id → users(id) ON DELETE CASCADE

---

#### 3. projects
Optional project grouping for todos.

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | CHAR(36) | NO | Primary key (UUID) |
| user_id | CHAR(36) | NO | Foreign key to users |
| name | VARCHAR(255) | NO | Project name |
| description | TEXT | YES | Project description |
| color | VARCHAR(7) | YES | Hex color code for UI |
| created_at | DATETIME | YES | Creation timestamp |

**Indexes:**
- PRIMARY KEY (id)
- KEY (user_id)

**Foreign Keys:**
- user_id → users(id) ON DELETE CASCADE

---

#### 4. todos
Main todo items.

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | CHAR(36) | NO | Primary key (UUID) |
| user_id | CHAR(36) | NO | Foreign key to users |
| title | VARCHAR(255) | NO | Todo title |
| description | TEXT | YES | Detailed description |
| status | ENUM | NO | open, in_progress, completed, archived |
| due_date | DATE | YES | Due date |
| due_time | TIME | YES | Due time |
| sync_enabled | BOOLEAN | NO | Sync with external services |
| reminder_enabled | BOOLEAN | NO | Enable reminders |
| recurring_enabled | BOOLEAN | NO | Mark as recurring |
| project_id | CHAR(36) | YES | Foreign key to projects |
| created_at | DATETIME | YES | Creation timestamp |
| updated_at | DATETIME | YES | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- KEY (user_id)
- KEY (due_date)
- KEY (status)

**Foreign Keys:**
- user_id → users(id) ON DELETE CASCADE
- project_id → projects(id) ON DELETE SET NULL

---

#### 5. todo_categories (Junction Table)
Many-to-many relationship between todos and categories.

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| todo_id | CHAR(36) | NO | Foreign key to todos |
| category_id | CHAR(36) | NO | Foreign key to categories |

**Indexes:**
- PRIMARY KEY (todo_id, category_id)

**Foreign Keys:**
- todo_id → todos(id) ON DELETE CASCADE
- category_id → categories(id) ON DELETE CASCADE

---

#### 6. recurring_tasks
Templates for recurring todo items.

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | CHAR(36) | NO | Primary key (UUID) |
| user_id | CHAR(36) | NO | Foreign key to users |
| title | VARCHAR(255) | NO | Task title |
| description | TEXT | YES | Task description |
| schedule | ENUM | NO | daily, weekly, monthly, custom |
| custom_days | JSON | YES | Array of days (e.g., ["mon","wed","fri"]) |
| favorite | BOOLEAN | NO | Mark as favorite |
| created_at | DATETIME | YES | Creation timestamp |
| updated_at | DATETIME | YES | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- KEY (user_id)

**Foreign Keys:**
- user_id → users(id) ON DELETE CASCADE

---

#### 7. recurring_task_categories (Junction Table)
Many-to-many relationship between recurring tasks and categories.

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| recurring_task_id | CHAR(36) | NO | Foreign key to recurring_tasks |
| category_id | CHAR(36) | NO | Foreign key to categories |

**Indexes:**
- PRIMARY KEY (recurring_task_id, category_id)

**Foreign Keys:**
- recurring_task_id → recurring_tasks(id) ON DELETE CASCADE
- category_id → categories(id) ON DELETE CASCADE

---

### Activity Logging

#### 8. activity_logs
Audit trail for user actions and system events.

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | CHAR(36) | NO | Primary key (UUID) |
| user_id | CHAR(36) | YES | Foreign key to users (nullable for anonymous) |
| action | VARCHAR(255) | NO | Action type (e.g., todo_created, login) |
| entity_type | VARCHAR(100) | YES | Entity type (todo, category, project, etc.) |
| entity_id | CHAR(36) | YES | Entity ID |
| details | JSON | YES | Additional metadata (before/after values) |
| ip_address | VARCHAR(45) | YES | User IP address |
| user_agent | TEXT | YES | Browser user agent |
| created_at | DATETIME | YES | Creation timestamp |

**Indexes:**
- PRIMARY KEY (id)
- KEY (user_id)
- KEY (created_at)
- KEY (action)
- KEY (entity_type, entity_id)

**Foreign Keys:**
- user_id → users(id) ON DELETE SET NULL

---

### Theme Marketplace

#### 9. marketplace_themes
Master list of available themes in the marketplace.

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | CHAR(36) | NO | Primary key (UUID) |
| name | VARCHAR(255) | NO | Theme identifier (unique) |
| display_name | VARCHAR(255) | NO | Human-readable name |
| description | TEXT | YES | Theme description |
| author | VARCHAR(255) | YES | Theme author |
| version | VARCHAR(50) | YES | Theme version |
| thumbnail_url | TEXT | YES | Preview image URL |
| download_url | TEXT | NO | Download URL |
| price | DECIMAL(10,2) | NO | Theme price (0 = free) |
| is_published | BOOLEAN | NO | Published status |
| metadata | JSON | YES | Tags, screenshots, etc. |
| created_at | DATETIME | YES | Creation timestamp |
| updated_at | DATETIME | YES | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE KEY (name)

---

#### 10. user_themes
Themes installed by users.

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | CHAR(36) | NO | Primary key (UUID) |
| user_id | CHAR(36) | NO | Foreign key to users |
| theme_id | CHAR(36) | NO | Foreign key to marketplace_themes |
| installed_at | DATETIME | YES | Installation timestamp |
| active | BOOLEAN | NO | Currently active theme |
| custom_settings | JSON | YES | User theme overrides |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE KEY (user_id, theme_id)

**Foreign Keys:**
- user_id → users(id) ON DELETE CASCADE
- theme_id → marketplace_themes(id) ON DELETE CASCADE

---

### AI Features

#### 11. ai_providers
Supported AI providers (OpenAI, Anthropic, Google, etc.).

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | CHAR(36) | NO | Primary key (UUID) |
| name | VARCHAR(100) | NO | Provider identifier (unique) |
| display_name | VARCHAR(255) | NO | Human-readable name |
| base_url | TEXT | YES | API endpoint override |
| is_builtin | BOOLEAN | NO | System vs custom provider |
| created_at | DATETIME | YES | Creation timestamp |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE KEY (name)

---

#### 12. user_api_keys
Encrypted API keys for each provider per user.

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | CHAR(36) | NO | Primary key (UUID) |
| user_id | CHAR(36) | NO | Foreign key to users |
| provider_id | CHAR(36) | NO | Foreign key to ai_providers |
| api_key_encrypted | TEXT | NO | Encrypted API key |
| label | VARCHAR(255) | YES | Key label (e.g., "Work Key") |
| is_active | BOOLEAN | NO | Active status |
| created_at | DATETIME | YES | Creation timestamp |
| last_used_at | DATETIME | YES | Last usage timestamp |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE KEY (user_id, provider_id)

**Foreign Keys:**
- user_id → users(id) ON DELETE CASCADE
- provider_id → ai_providers(id) ON DELETE CASCADE

---

#### 13. user_ai_settings
Per-user AI preferences.

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| user_id | CHAR(36) | NO | Primary key, Foreign key to users |
| default_provider_id | CHAR(36) | YES | Foreign key to ai_providers |
| default_model | VARCHAR(100) | YES | Default model (e.g., gpt-4) |
| max_tokens | INT | NO | Maximum tokens (default: 2048) |
| temperature | FLOAT | NO | Temperature (default: 0.7) |
| updated_at | DATETIME | YES | Last update timestamp |

**Indexes:**
- PRIMARY KEY (user_id)

**Foreign Keys:**
- user_id → users(id) ON DELETE CASCADE
- default_provider_id → ai_providers(id) ON DELETE SET NULL

---

#### 14. ai_chats
AI conversation threads.

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | CHAR(36) | NO | Primary key (UUID) |
| user_id | CHAR(36) | NO | Foreign key to users |
| title | VARCHAR(255) | YES | Chat title |
| provider_id | CHAR(36) | YES | Foreign key to ai_providers |
| model_used | VARCHAR(100) | YES | Model snapshot |
| system_prompt | TEXT | YES | Custom system prompt |
| created_at | DATETIME | YES | Creation timestamp |
| updated_at | DATETIME | YES | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- KEY (user_id)
- KEY (updated_at)

**Foreign Keys:**
- user_id → users(id) ON DELETE CASCADE
- provider_id → ai_providers(id) ON DELETE SET NULL

---

#### 15. ai_messages
Individual messages in AI chats.

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | CHAR(36) | NO | Primary key (UUID) |
| chat_id | CHAR(36) | NO | Foreign key to ai_chats |
| role | ENUM | NO | user, assistant, system |
| content | TEXT | NO | Message content |
| tokens_used | INT | YES | Token count for billing |
| created_at | DATETIME | YES | Creation timestamp |

**Indexes:**
- PRIMARY KEY (id)
- KEY (chat_id)

**Foreign Keys:**
- chat_id → ai_chats(id) ON DELETE CASCADE

---

## Entity Relationship Diagram (ERD)

```
┌─────────────────┐
│     users       │
├─────────────────┤
│ id (PK)         │◄────────┐
│ email           │         │
│ password_hash   │         │
│ name            │         │
│ settings        │         │
│ created_at      │         │
│ updated_at      │         │
└─────────────────┘         │
                            │
        ┌───────────────────┼───────────────────┐
        │                   │                   │
        │                   │                   │
┌───────────────┐    ┌───────────────┐    ┌───────────────┐
│  categories   │    │   projects    │    │ activity_logs │
├───────────────┤    ├───────────────┤    ├───────────────┤
│ id (PK)       │    │ id (PK)       │    │ id (PK)       │
│ user_id (FK)  │    │ user_id (FK)  │    │ user_id (FK)  │
│ name          │    │ name          │    │ action        │
│ color         │    │ description   │    │ entity_type   │
│ favorite      │    │ color         │    │ entity_id     │
│ created_at    │    │ created_at    │    │ details       │
└───────────────┘    └───────────────┘    │ ip_address    │
        │                   │               │ user_agent    │
        │                   │               │ created_at    │
        │                   │               └───────────────┘
        │                   │
        │                   │
        │                   │
┌───────────────┐    ┌───────────────┐
│     todos      │    │recurring_tasks│
├───────────────┤    ├───────────────┤
│ id (PK)       │    │ id (PK)       │
│ user_id (FK)  │    │ user_id (FK)  │
│ title         │    │ title         │
│ description   │    │ description   │
│ status        │    │ schedule      │
│ due_date      │    │ custom_days   │
│ due_time      │    │ favorite      │
│ project_id(FK)│   │ created_at    │
│ created_at    │    │ updated_at    │
│ updated_at    │    └───────────────┘
└───────────────┘            │
        │                   │
        │                   │
        │                   │
┌──────────────────┐        │
│  todo_categories │◄───────┘
├──────────────────┤
│ todo_id (PK,FK)  │
│ category_id(PK,FK)│
└──────────────────┘
        │
        │
┌──────────────────────────┐
│recurring_task_categories │
├──────────────────────────┤
│ recurring_task_id (PK,FK) │
│ category_id (PK,FK)       │
└──────────────────────────┘

┌─────────────────┐
│marketplace_themes│
├─────────────────┤
│ id (PK)         │◄────────┐
│ name            │         │
│ display_name    │         │
│ description     │         │
│ download_url    │         │
│ price           │         │
│ is_published    │         │
└─────────────────┘         │
                            │
                            │
                  ┌─────────┴─────────┐
                  │   user_themes    │
                  ├──────────────────┤
                  │ id (PK)          │
                  │ user_id (FK)     │
                  │ theme_id (FK)    │
                  │ active           │
                  │ custom_settings  │
                  └──────────────────┘

┌─────────────┐
│ai_providers │
├─────────────┤
│ id (PK)     │◄────────┐
│ name        │         │
│ display_name│         │
│ base_url    │         │
│ is_builtin  │         │
└─────────────┘         │
                         │
         ┌───────────────┼──────────────┐
         │               │              │
┌────────────────┐ ┌──────────────┐ ┌─────────────────┐
│  user_api_keys │ │user_ai_settin│ │    ai_chats     │
├────────────────┤ ├──────────────┤ ├─────────────────┤
│ id (PK)        │ │user_id (PK)  │ │ id (PK)         │
│ user_id (FK)   │ │default_prv(FK)│ │ user_id (FK)    │
│ provider_id(FK) │ │default_model │ │ provider_id(FK) │
│ api_key_enc    │ │max_tokens    │ │ title           │
│ label          │ │temperature  │ │ model_used      │
│ is_active      │ │updated_at   │ │ system_prompt   │
│ last_used_at   │ └──────────────┘ │ created_at      │
└────────────────┘                  │ updated_at      │
                                   └─────────────────┘
                                            │
                                            │
                                   ┌────────────────┐
                                   │  ai_messages   │
                                   ├────────────────┤
                                   │ id (PK)        │
                                   │ chat_id (FK)   │
                                   │ role           │
                                   │ content        │
                                   │ tokens_used    │
                                   │ created_at     │
                                   └────────────────┘
```

## Relationships Summary

| Entity | Relations |
|--------|-----------|
| **users** | Has many: todos, categories, projects, recurring_tasks, activity_logs, user_themes, user_api_keys, ai_chats, user_ai_settings |
| **todos** | Belongs to: user, project (optional). Many-to-many with categories via todo_categories |
| **recurring_tasks** | Belongs to: user. Many-to-many with categories via recurring_task_categories |
| **categories** | Linked to: todos, recurring_tasks |
| **marketplace_themes** | Installed by users via user_themes |
| **ai_providers** | Referenced by: user_api_keys, ai_chats, user_ai_settings |
| **ai_chats** | Belongs to: user, provider (optional). Has many: ai_messages |
| **ai_messages** | Belongs to: chat |

## Key Design Decisions

1. **UUID Primary Keys**: Using CHAR(36) for UUIDs to support distributed systems and prevent ID enumeration attacks.

2. **Foreign Key Cascades**: 
   - CASCADE DELETE for user-owned entities to clean up data when users are deleted
   - SET NULL for optional references (e.g., project_id in todos)

3. **JSON Fields**: Used for flexible data like settings, custom_days, and metadata.

4. **Junction Tables**: Proper normalization for many-to-many relationships (todo-categories, recurring_task-categories).

5. **Activity Logging**: Nullable user_id allows for anonymous/system events.

6. **Theme Marketplace**: Separation of global theme catalog and user installations.

7. **AI Multi-Provider**: Support for multiple AI providers with per-user encrypted API keys.

## Migration and Seeding

To set up the database:

```bash
# Run all migrations
php spark migrate

# Run seeders
php spark db:seed AiProvidersSeeder
php spark db:seed MarketplaceThemesSeeder
php spark db:seed SampleDataSeeder
```

## Model Files

All tables have corresponding CodeIgniter 4 models in `app/Models/`:

- UserModel
- CategoryModel
- ProjectModel
- TodoModel
- TodoCategoryModel
- RecurringTaskModel
- RecurringTaskCategoryModel
- ActivityLogModel
- MarketplaceThemeModel
- UserThemeModel
- AiProviderModel
- UserApiKeyModel
- UserAiSettingsModel
- AiChatModel
- AiMessageModel

Each model includes:
- Validation rules
- Timestamp handling
- Custom query methods for common operations
- Relationship helpers
