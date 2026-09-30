# Changelog

All notable changes to `rahad9999/activitylog` will be documented in this file.

## [1.1.1] - 2026-09-30

### Added
- Dedicated User Search input field in the Blade dashboard view (`index.blade.php`) for fast searching by name, email, or user ID.
- Properly vertically centered search and user search icons.
- Clickable user names in table rows for instant single-click user filtering (desktop and mobile).
- `user` query parameter filter support in `ActivityLogController` and `ActivityLogApiController`.
- `users` list included in `GET /api/activity-logs/filter-options` endpoint response.
- Accurate active filter detection so the "Clear" button and sidebar "Clear date filter" only display when active filters are applied and properly disappear on reset.
- Comprehensive feature test suite additions for user filtering across Web and REST API.

## [1.0.0] - 2026-09-17

### Initial Release
- `ActivityLog` Eloquent model with polymorphic causer and subject relationships.
- Static logging helpers (`record`, `recordCreated`, `recordUpdated`, `recordDeleted`, `recordCustom`).
- Global `activity_log()` helper function.
- `LogsActivity` trait for automatic Eloquent model lifecycle tracking.
- `HasActivityLogs` trait for causer and subject relationship convenience.
- Interactive Blade timeline dashboard with date groups, live search, multi-filter dropdowns, and bulk delete.
- Full RESTful API with endpoints for listing, date grouping, filtering, detail inspection, single delete, bulk delete, and date group delete.
- Interactive Artisan commands: `activitylog:install` and `activitylog:clean` (with date prompt).
- Comprehensive REST API documentation (`docs/api.md`).
