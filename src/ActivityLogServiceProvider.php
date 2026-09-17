<?php

namespace Rahat\ActivityLog;

use Illuminate\Support\ServiceProvider;
use Rahat\ActivityLog\Commands\CleanActivityLogCommand;
use Rahat\ActivityLog\Commands\InstallActivityLogCommand;

class ActivityLogServiceProvider extends ServiceProvider
{
    /**
     * Register any package services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/activitylog.php',
            'activitylog'
        );
    }

    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        // 1. Load Package Views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'activitylog');

        // 2. Load Package Routes
        if (config('activitylog.web.enabled', true)) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        }

        if (config('activitylog.api.enabled', true)) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        }

        // 3. Console & Publishing
        if ($this->app->runningInConsole()) {
            // Register Console Commands
            $this->commands([
                InstallActivityLogCommand::class,
                CleanActivityLogCommand::class,
            ]);

            // Publish Configuration
            $this->publishes([
                __DIR__ . '/../config/activitylog.php' => config_path('activitylog.php'),
            ], 'activitylog-config');

            // Publish Views
            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/activitylog'),
            ], 'activitylog-views');

            // Publish Migrations
            $this->publishes([
                __DIR__ . '/../database/migrations/create_activity_logs_table.php.stub' => database_path('migrations/' . date('Y_m_d_His') . '_create_' . config('activitylog.table_name', 'activity_logs') . '_table.php'),
            ], 'activitylog-migrations');
        }
    }
}
