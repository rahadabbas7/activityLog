# ActivityLog REST API Documentation

The `rahat/activitylog` package provides a full-featured, RESTful API for listing, filtering, inspecting, and managing activity logs in headless applications, mobile apps, or single-page applications (Vue, React, Svelte).

---

## Base URL & Configuration

By default, the API routes are registered under:
```
/api/activity-logs
```

You can customize the route prefix, middleware, and pagination limit in `config/activitylog.php`:

```php
'api' => [
    'enabled' => true,
    'route_prefix' => 'api/activity-logs',
    'middleware' => ['api', 'auth:sanctum'],
    'per_page' => 20,
],
```

---

## Authentication & Headers

Requests should include standard JSON API headers:

```http
Accept: application/json
Content-Type: application/json
Authorization: Bearer <your-api-token>
```

---

## Endpoints Overview

| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/api/activity-logs` | List activity logs with search, filters, and pagination |
| `GET` | `/api/activity-logs/date-groups` | Retrieve aggregated date timeline groups with record counts |
| `GET` | `/api/activity-logs/filter-options` | Retrieve available unique filter values (modules, roles, actions) |
| `GET` | `/api/activity-logs/{id}` | Retrieve details of a single activity log |
| `DELETE` | `/api/activity-logs/{id}` | Delete a single activity log |
| `POST` | `/api/activity-logs/bulk-delete` | Bulk delete multiple activity logs by an array of IDs |
| `DELETE` | `/api/activity-logs/date-group/{date}` | Delete all activity logs for a specific date (YYYY-MM-DD) |

---

## 1. List Activity Logs

Retrieve a paginated list of activity log records with comprehensive filtering and search.

- **URL**: `GET /api/activity-logs`
- **Query Parameters**:

| Parameter | Type | Required | Description |
| :--- | :--- | :--- | :--- |
| `search` | `string` | No | Search query matching title, module, action, role, or causer name/email |
| `role` | `string` | No | Filter by user role (e.g. `admin`, `teacher`, `student`) |
| `module` | `string` | No | Filter by module name (e.g. `User`, `Wallet`, `Course`) |
| `action` | `string` | No | Filter by action (e.g. `created`, `updated`, `deleted`, `login`) |
| `selected_date` | `string` | No | Filter for a single date (`YYYY-MM-DD`) |
| `date_from` | `string` | No | Start date filter (`YYYY-MM-DD`) |
| `date_to` | `string` | No | End date filter (`YYYY-MM-DD`) |
| `per_page` | `integer`| No | Items per page (default: `20`) |
| `page` | `integer`| No | Page number (default: `1`) |

### Example Request
```http
GET /api/activity-logs?module=User&action=updated&page=1 HTTP/1.1
Host: example.com
Authorization: Bearer <token>
Accept: application/json
```

### Example Response (`200 OK`)
```json
{
  "success": true,
  "message": "Activity logs retrieved successfully.",
  "data": [
    {
      "id": 142,
      "log_name": "default",
      "title": "User profile updated",
      "module": "User",
      "action": "updated",
      "role": "admin",
      "causer": {
        "id": 1,
        "type": "User",
        "name": "Rahat Admin"
      },
      "subject": {
        "id": 18,
        "type": "User",
        "name": "John Doe"
      },
      "created_at": "2026-09-17T07:15:32.000000Z",
      "created_at_diff": "5 minutes ago",
      "created_at_formatted": "Sep 17, 2026 01:15 PM"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 20,
    "total": 92
  }
}
```

---

## 2. Retrieve Date Timeline Groups

Returns an aggregated list of recent dates (up to 30 days) with the total count of activities for each date. Ideal for building interactive timeline sidebars.

- **URL**: `GET /api/activity-logs/date-groups`

### Example Response (`200 OK`)
```json
{
  "success": true,
  "message": "Activity date groups retrieved successfully.",
  "data": [
    {
      "date": "2026-09-17",
      "label": "Today",
      "count": 28
    },
    {
      "date": "2026-09-16",
      "label": "Yesterday",
      "count": 45
    },
    {
      "date": "2026-09-15",
      "label": "Sep 15",
      "count": 19
    }
  ]
}
```

---

## 3. Retrieve Filter Options

Returns all distinct values currently present in the database for `modules`, `roles`, and `actions` to dynamically populate dropdown filters in your UI.

- **URL**: `GET /api/activity-logs/filter-options`

### Example Response (`200 OK`)
```json
{
  "success": true,
  "message": "Filter options retrieved successfully.",
  "data": {
    "modules": [
      "Authentication",
      "Course",
      "Support",
      "User",
      "Wallet"
    ],
    "roles": [
      "admin",
      "instructor",
      "student"
    ],
    "actions": [
      "created",
      "deleted",
      "login",
      "logout",
      "updated"
    ]
  }
}
```

---

## 4. Retrieve Single Activity Log Details

Retrieve full details of a specific activity log record, including old vs new value diffs, extra metadata, and HTTP request context.

- **URL**: `GET /api/activity-logs/{id}`

### Example Response (`200 OK`)
```json
{
  "success": true,
  "message": "Activity log details retrieved successfully.",
  "data": {
    "id": 142,
    "log_name": "default",
    "title": "User profile updated",
    "module": "User",
    "action": "updated",
    "role": "admin",
    "causer": {
      "id": 1,
      "type": "App\\Models\\User",
      "name": "Rahat Admin"
    },
    "subject": {
      "id": 18,
      "type": "App\\Models\\User",
      "name": "John Doe"
    },
    "old_values": {
      "email": "john.old@example.com",
      "status": "pending"
    },
    "new_values": {
      "email": "john.new@example.com",
      "status": "active"
    },
    "metadata": {
      "ip_country": "US",
      "reason": "Admin approval"
    },
    "request": {
      "ip_address": "127.0.0.1",
      "method": "PUT",
      "url": "https://example.com/api/v1/users/18",
      "user_agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64)..."
    },
    "created_at": "2026-09-17T07:15:32.000000Z",
    "created_at_diff": "5 minutes ago",
    "created_at_formatted": "Sep 17, 2026 01:15:32 PM"
  }
}
```

### Error Response (`404 Not Found`)
```json
{
  "success": false,
  "message": "Activity log not found."
}
```

---

## 5. Delete Single Activity Log

Delete a specific activity log entry by ID.

- **URL**: `DELETE /api/activity-logs/{id}`

### Example Response (`200 OK`)
```json
{
  "success": true,
  "message": "Activity log deleted successfully."
}
```

---

## 6. Bulk Delete Activity Logs

Permanently delete multiple activity logs in a single batch request.

- **URL**: `POST /api/activity-logs/bulk-delete`
- **Request Body**:

```json
{
  "ids": [101, 102, 103, 104]
}
```

### Validation Rules:
- `ids`: Required, array, minimum 1 item.
- `ids.*`: Required, integer.

### Example Response (`200 OK`)
```json
{
  "success": true,
  "message": "4 activity log(s) deleted successfully.",
  "data": {
    "deleted_count": 4
  }
}
```

---

## 7. Delete Date Group Activity Logs

Permanently delete all activity logs created on a specific calendar date (`YYYY-MM-DD`).

- **URL**: `DELETE /api/activity-logs/date-group/{date}`

### Example Request:
```http
DELETE /api/activity-logs/date-group/2026-09-15 HTTP/1.1
Host: example.com
Authorization: Bearer <token>
Accept: application/json
```

### Example Response (`200 OK`)
```json
{
  "success": true,
  "message": "19 activity log(s) deleted for 2026-09-15.",
  "data": {
    "date": "2026-09-15",
    "deleted_count": 19
  }
}
```
