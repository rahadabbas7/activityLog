# Laravel ActivityLog (`rahat/activitylog`)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/rahat/activitylog.svg?style=flat-square)](https://packagist.org/packages/rahat/activitylog)
[![Total Downloads](https://img.shields.io/packagist/dt/rahat/activitylog.svg?style=flat-square)](https://packagist.org/packages/rahat/activitylog)
[![License](https://img.shields.io/packagist/l/rahat/activitylog.svg?style=flat-square)](LICENSE.md)

A clean, lightweight, and powerful activity logging system for Laravel applications with **pure Laravel Blade UI**, **standard Controllers** (no Livewire required), and a **full RESTful API**.

---

## ✨ Features

- 🎯 **Simple & Direct API**: Record logs directly with `ActivityLog::record()`, `ActivityLog::recordCreated()`, `ActivityLog::recordUpdated()`, `ActivityLog::recordDeleted()`, `ActivityLog::recordCustom()`, or `activity_log()` helper.
- ⚡ **Auto-Tracking Eloquent Trait**: Add `LogsActivity` trait to any Eloquent model to automatically track `created`, `updated` (dirty diffs), `deleted`, and `restored` events.
- 🖥️ **Interactive Pure Blade Dashboard**: Responsive timeline sidebar with date groups, live search, multi-filter dropdowns, bulk delete, and single log inspection without needing Livewire.
- 🌐 **Full RESTful API**: Built-in endpoints for listing, filtering, inspecting, bulk deleting, and managing date groups for mobile & headless apps. ([View API Documentation](docs/api.md))
- 🧹 **Interactive Clean Artisan Command**: `php artisan activitylog:clean` prompts for specific dates or automatically prunes logs older than retention days.
- 🎨 **Dark / Light Mode**: Built-in support for theme switching with Tailwind CSS.

---

## 📦 Installation

You can install the package via Composer:

```bash
composer require rahat/activitylog
```

Run the interactive installer to publish the configuration and migrations:

```bash
php artisan activitylog:install
```

Or publish them manually:

```bash
php artisan vendor:publish --tag="activitylog-config"
php artisan vendor:publish --tag="activitylog-migrations"
php artisan migrate
```

---

## ⚙️ Configuration

The package configuration file is saved at `config/activitylog.php`:

```php
return [
    // Table name
    'table_name' => 'activity_logs',

    // Model class
    'model' => \Rahat\ActivityLog\Models\ActivityLog::class,

    // Global toggle
    'enabled' => true,

    // Automatic retention in days (used by cleaner command)
    'delete_records_older_than_days' => 365,

    // Web UI Settings
    'web' => [
        'enabled' => true,
        'route_prefix' => 'activity-logs',
        'middleware' => ['web', 'auth'],
        'layout' => 'activitylog::layouts.blank',
        'title' => 'Activity Log',
        'per_page' => 20,
        'home_url' => '/',
    ],

    // REST API Settings
    'api' => [
        'enabled' => true,
        'route_prefix' => 'api/activity-logs',
        'middleware' => ['api', 'auth:sanctum'],
        'per_page' => 20,
    ],
];
```

---

## 🚀 Usage

### 1. Direct Static Logging

```php
use Rahat\ActivityLog\Models\ActivityLog;

// Generic log
ActivityLog::record(
    title: 'User updated profile',
    module: 'User',
    action: 'updated',
    subject: $user,
    old: ['email' => 'old@example.com'],
    new: ['email' => 'new@example.com'],
    metadata: ['ip_country' => 'US']
);

// Event shortcuts
ActivityLog::recordCreated(module: 'Course', subject: $course, new: $course->toArray());
ActivityLog::recordUpdated(module: 'Course', subject: $course, old: $old, new: $new);
ActivityLog::recordDeleted(module: 'Course', subject: $course, old: $old);
ActivityLog::recordCustom(title: 'User logged in', module: 'Auth', action: 'login');
```

### 2. Global Helper Function

```php
// Quick custom log
activity_log('Password changed', 'Auth', 'password_change', $user);

// Query builder
$recentLogs = activity_log()->module('User')->today()->get();
```

### 3. Automatic Eloquent Model Tracking

Add the `LogsActivity` trait to any Eloquent model to automatically track changes:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Rahat\ActivityLog\Concerns\LogsActivity;

class Post extends Model
{
    use LogsActivity;

    // Optional: customize the module name
    protected string $activityModule = 'Blog';

    // Optional: ignore specific attributes from tracking
    protected array $activityIgnoredAttributes = ['view_count', 'updated_at'];
}
```

### 4. Querying Logs on Models

Add the `HasActivityLogs` trait to your `User` model:

```php
use Rahat\ActivityLog\Concerns\HasActivityLogs;

class User extends Authenticatable
{
    use HasActivityLogs;
}

// Access activities performed by this user:
$user->activitiesAsCauser;

// Access activities where this model was the subject:
$post->activitiesAsSubject;
```

---

## 🧹 Cleaning & Pruning Logs

Clean activity logs using the artisan command:

```bash
# Interactive mode (prompts to enter a date or use retention policy):
php artisan activitylog:clean

# Delete logs for a specific date:
php artisan activitylog:clean --date=2026-09-15

# Delete logs older than 30 days:
php artisan activitylog:clean --days=30

# Force delete without confirmation:
php artisan activitylog:clean --date=2026-09-15 --force
```

---

## 🌐 Web Dashboard & API

### Web Dashboard
Navigate to:
```
http://your-domain.test/activity-logs
```

### RESTful API
Access JSON endpoints under:
```
http://your-domain.test/api/activity-logs
```

For full API endpoint specifications and JSON schema examples, see [docs/api.md](docs/api.md).

---

## 🧪 Testing

```bash
composer test
```

---

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
