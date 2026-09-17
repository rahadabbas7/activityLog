<?php

namespace Rahat\ActivityLog\Tests\Unit;

use Carbon\Carbon;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Rahat\ActivityLog\Concerns\HasActivityLogs;
use Rahat\ActivityLog\Concerns\LogsActivity;
use Rahat\ActivityLog\Models\ActivityLog;
use Rahat\ActivityLog\Tests\TestCase;

class TestUser extends Authenticatable
{
    use HasActivityLogs;

    protected $table = 'users';
    protected $guarded = [];
}

class TestPost extends Authenticatable
{
    use LogsActivity, HasActivityLogs;

    protected $table = 'posts';
    protected $guarded = [];
    protected string $activityModule = 'Blog';
}

class ActivityLogModelTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        \Illuminate\Support\Facades\Schema::create('posts', function ($table) {
            $table->id();
            $table->string('title');
            $table->text('body')->nullable();
            $table->timestamps();
        });
    }

    public function test_it_can_create_an_activity_log()
    {
        $user = TestUser::create([
            'name' => 'Rahat',
            'email' => 'rahat@example.com',
            'role' => 'admin',
        ]);

        $this->actingAs($user);

        $log = ActivityLog::record(
            title: 'Created new article',
            module: 'Articles',
            action: 'created',
            subject: $user,
            old: [],
            new: ['name' => 'Rahat'],
            metadata: ['device' => 'desktop']
        );

        $this->assertInstanceOf(ActivityLog::class, $log);
        $this->assertEquals('Articles', $log->module);
        $this->assertEquals('created', $log->action);
        $this->assertEquals('Rahat', $log->causer_name);
        $this->assertEquals('admin', $log->role);
        $this->assertEquals(['name' => 'Rahat'], $log->new_values);
    }

    public function test_it_can_use_query_scopes()
    {
        ActivityLog::record(title: 'A', module: 'Auth', action: 'login');
        ActivityLog::record(title: 'B', module: 'Auth', action: 'logout');
        ActivityLog::record(title: 'C', module: 'Billing', action: 'charge');

        $this->assertEquals(2, ActivityLog::module('Auth')->count());
        $this->assertEquals(1, ActivityLog::action('login')->count());
        $this->assertEquals(1, ActivityLog::module('Billing')->count());
    }

    public function test_logs_activity_trait_automatically_logs_model_events()
    {
        $post = TestPost::create([
            'title' => 'First Post',
            'body' => 'Hello World',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'module' => 'Blog',
            'action' => 'created',
            'title' => 'TestPost created',
        ]);

        $post->update(['title' => 'Updated Post Title']);

        $this->assertDatabaseHas('activity_logs', [
            'module' => 'Blog',
            'action' => 'updated',
            'title' => 'TestPost updated',
        ]);

        $post->delete();

        $this->assertDatabaseHas('activity_logs', [
            'module' => 'Blog',
            'action' => 'deleted',
            'title' => 'TestPost deleted',
        ]);
    }
}
