<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Activity Log Database Table
    |--------------------------------------------------------------------------
    |
    | The table name used by the package to store activity log entries.
    |
    */

    'table_name' => env('ACTIVITY_LOG_TABLE', 'activity_logs'),

    /*
    |--------------------------------------------------------------------------
    | Activity Log Model
    |--------------------------------------------------------------------------
    |
    | The Eloquent model class used to represent activity log records.
    | You can extend Rahad\ActivityLog\Models\ActivityLog and specify your custom model here.
    |
    */

    'model' => \Rahad\ActivityLog\Models\ActivityLog::class,

    /*
    |--------------------------------------------------------------------------
    | Enable / Disable Logging
    |--------------------------------------------------------------------------
    |
    | Global switch to enable or disable recording activity logs.
    |
    */

    'enabled' => env('ACTIVITY_LOG_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Automatic Log Retention (Days)
    |--------------------------------------------------------------------------
    |
    | Number of days before activity log records become eligible for pruning
    | when running `php artisan activitylog:clean`. Set to null to keep forever.
    |
    */

    'delete_records_older_than_days' => 365,

    /*
    |--------------------------------------------------------------------------
    | Causer & Role Detection
    |--------------------------------------------------------------------------
    |
    | Configuration for resolving the user/causer who triggered the activity,
    | and capturing their current role (e.g. Spatie Permission role or column).
    |
    */

    'causer' => [
        // Resolves the authenticated user
        'resolver' => null, // null defaults to auth()->user()

        // Attribute or callback to snapshot the causer's role
        'role_resolver' => null, // null defaults to Spatie role ($user->roles->first()?->name) or $user->role
    ],

    /*
    |--------------------------------------------------------------------------
    | Request Information Tracking
    |--------------------------------------------------------------------------
    |
    | Automatically capture HTTP request metadata with each activity log.
    |
    */

    'request' => [
        'log_ip_address' => true,
        'log_user_agent' => true,
        'log_url' => true,
        'log_method' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Web UI Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the built-in Blade dashboard interface.
    |
    */

    'web' => [
        'enabled' => true,
        'route_prefix' => 'activity-logs',
        'middleware' => ['web', 'auth'],
        'layout' => 'activitylog::layouts.blank', // 'activitylog::layouts.blank' or 'layouts.app'
        'title' => 'Activity Log',
        'per_page' => 20,
        'home_url' => '/',
    ],

    /*
    |--------------------------------------------------------------------------
    | REST API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the RESTful JSON endpoints.
    |
    */

    'api' => [
        'enabled' => true,
        'route_prefix' => 'api/activity-logs',
        'middleware' => ['api'], // e.g. ['api', 'auth:sanctum']
        'per_page' => 20,
    ],

];
