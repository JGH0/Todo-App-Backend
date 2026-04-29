# Automatic Activity Logging Guide

## Overview

The Todo App Backend now includes automatic activity logging for all CRUD operations (Create, Read, Update, Delete) on key entities. This is implemented using a PHP trait that can be easily added to any model.

## How It Works

### LoggableTrait

The `LoggableTrait` in `app/Models/LoggableTrait.php` provides automatic logging functionality by hooking into CodeIgniter 4's model lifecycle events:

- **afterInsert**: Logs when a new record is created
- **afterUpdate**: Logs when a record is updated
- **afterDelete**: Logs when a record is deleted

### Enabled Models

The following models now have automatic logging enabled:

1. **UserModel** - Logs user creation, updates, and deletion
2. **TodoModel** - Logs todo creation, updates, and deletion
3. **CategoryModel** - Logs category creation, updates, and deletion
4. **ProjectModel** - Logs project creation, updates, and deletion
5. **RecurringTaskModel** - Logs recurring task creation, updates, and deletion

### What Gets Logged

Each log entry includes:

- **user_id**: The ID of the user performing the action (from the record or session)
- **action**: Formatted action name (e.g., "todo_created", "user_updated", "category_deleted")
- **entity_type**: The type of entity (e.g., "todo", "user", "category")
- **entity_id**: The ID of the affected entity
- **details**: JSON object with relevant fields (title, name, email, etc.)
- **ip_address**: Client IP address
- **user_agent**: Browser user agent string
- **created_at**: Timestamp of the action

### Example Log Entries

**Creating a todo:**
```json
{
  "user_id": "550e8400-e29b-41d4-a716-446655440000",
  "action": "todo_created",
  "entity_type": "todo",
  "entity_id": "550e8400-e29b-41d4-a716-446655440001",
  "details": {
    "action": "created",
    "title": "Complete project documentation"
  },
  "ip_address": "192.168.1.100",
  "user_agent": "Mozilla/5.0...",
  "created_at": "2026-04-29 13:42:00"
}
```

**Updating a user:**
```json
{
  "user_id": "550e8400-e29b-41d4-a716-446655440000",
  "action": "user_updated",
  "entity_type": "user",
  "entity_id": "550e8400-e29b-41d4-a716-446655440000",
  "details": {
    "action": "updated",
    "name": "John Doe",
    "email": "john@example.com"
  },
  "ip_address": "192.168.1.100",
  "user_agent": "Mozilla/5.0...",
  "created_at": "2026-04-29 13:45:00"
}
```

**Deleting a category:**
```json
{
  "user_id": "550e8400-e29b-41d4-a716-446655440000",
  "action": "category_deleted",
  "entity_type": "category",
  "entity_id": "550e8400-e29b-41d4-a716-446655440002",
  "details": {
    "action": "deleted",
    "name": "Work"
  },
  "ip_address": "192.168.1.100",
  "user_agent": "Mozilla/5.0...",
  "created_at": "2026-04-29 13:50:00"
}
```

## Adding Logging to Other Models

To add automatic logging to a new model:

1. Add the `use LoggableTrait;` statement to your model class
2. Override the `getEntityType()` method to return the entity type name

Example:
```php
<?php

namespace App\Models;

use CodeIgniter\Model;

class MarketplaceThemeModel extends Model
{
    use LoggableTrait;

    protected $table = 'marketplace_themes';
    // ... other model properties ...

    protected function getEntityType(): string
    {
        return 'marketplace_theme';
    }
}
```

## Customizing Log Details

You can customize what details are logged by overriding the `getLogDetails()` method in your model:

```php
protected function getLogDetails($action, $data): array
{
    $details = parent::getLogDetails($action, $data);

    // Add custom fields
    if (isset($data['price'])) {
        $details['price'] = $data['price'];
    }
    if (isset($data['is_published'])) {
        $details['is_published'] = $data['is_published'];
    }

    return $details;
}
```

## Querying Activity Logs

Use the `ActivityLogModel` to query logs:

```php
$activityLogModel = new ActivityLogModel();

// Get logs for a specific user
$logs = $activityLogModel->getByUser($userId);

// Get logs for a specific entity
$logs = $activityLogModel->getByEntity('todo', $todoId);

// Get logs by action type
$logs = $activityLogModel->getByAction('todo_created');

// Custom query
$logs = $activityLogModel
    ->where('user_id', $userId)
    ->where('entity_type', 'todo')
    ->orderBy('created_at', 'DESC')
    ->limit(20)
    ->get()
    ->getResultArray();
```

## Session Requirement

The logging system attempts to get the user ID from:
1. The data being inserted/updated (e.g., `user_id` field in the record)
2. The session (`session()->get('user_id')`)

Make sure your authentication system sets the user ID in the session for proper logging.

## Disabling Logging

To disable automatic logging for a specific operation, you can temporarily disable the model events:

```php
$todoModel = new TodoModel();
$todoModel->disableEvents();
$todoModel->insert($data);
$todoModel->enableEvents();
```

Or remove the `use LoggableTrait;` from the model class entirely.
