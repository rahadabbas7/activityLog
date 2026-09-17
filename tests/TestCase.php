<?php

namespace Rahad\ActivityLog\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;
use Rahad\ActivityLog\ActivityLogServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
    }

    protected function getPackageProviders($app): array
    {
        return [
            ActivityLogServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
        config()->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        config()->set('app.key', 'base64:6Cu/ozUs4dpPZFslK/Yqh0ET10CK53EdmVRqqUtc13U=');
        config()->set('activitylog.api.middleware', ['api']);
    }

    protected function setUpDatabase(): void
    {
        // Run package migration
        $migration = include __DIR__ . '/../database/migrations/create_activity_logs_table.php.stub';
        $migration->up();

        // Create dummy users table for tests
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('role')->default('user');
            $table->timestamps();
        });
    }
}
