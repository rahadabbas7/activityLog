<?php

namespace Rahad\ActivityLog\Tests\Unit;

use Carbon\Carbon;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Rahad\ActivityLog\Concerns\HasActivityLogs;
use Rahad\ActivityLog\Concerns\LogsActivity;
use Rahad\ActivityLog\Models\ActivityLog;
use Rahad\ActivityLog\Tests\TestCase;

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
            'name' => 'Rahad',
            'email' => 'rahad@example.com',
            'role' => 'admin',
        ]);

        $this->actingAs($user);

        $log = ActivityLog::record(
            title: 'Created new article',
            module: 'Articles',
            action: 'created',
            subject: $user,
            old: [],
            new: ['name' => 'Rahad'],
            metadata: ['device' => 'desktop']
        );

        $this->assertInstanceOf(ActivityLog::class, $log);
        $this->assertEquals('Articles', $log->module);
        $this->assertEquals('created', $log->action);
        $this->assertEquals('Rahad', $log->causer_name);
        $this->assertEquals('admin', $log->role);
        $this->assertEquals(['name' => 'Rahad'], $log->new_values);
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
