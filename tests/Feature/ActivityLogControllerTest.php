<?php

namespace Rahad\ActivityLog\Tests\Feature;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Rahad\ActivityLog\Models\ActivityLog;
use Rahad\ActivityLog\Tests\TestCase;

class WebTestUser extends Authenticatable
{
    protected $table = 'users';
    protected $guarded = [];
}

class ActivityLogControllerTest extends TestCase
{
    public function test_it_can_render_index_page()
    {
        $user = WebTestUser::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);

        ActivityLog::record(title: 'Log 1', module: 'Auth', action: 'login');
        ActivityLog::record(title: 'Log 2', module: 'Course', action: 'created');

        $response = $this->actingAs($user)->get(route('activitylog.index'));

        $response->assertStatus(200);
        $response->assertSee('Activity Log');
    }

    public function test_it_can_render_show_page()
    {
        $user = WebTestUser::create([
            'name' => 'Admin User',
            'email' => 'admin2@example.com',
            'role' => 'admin',
        ]);

        $log = ActivityLog::record(title: 'Profile Updated', module: 'User', action: 'updated');

        $response = $this->actingAs($user)->get(route('activitylog.view', $log->id));

        $response->assertStatus(200);
        $response->assertSee('Profile Updated');
    }

    public function test_it_can_delete_a_log()
    {
        $user = WebTestUser::create([
            'name' => 'Admin User',
            'email' => 'admin3@example.com',
            'role' => 'admin',
        ]);

        $log = ActivityLog::record(title: 'To Delete', module: 'User', action: 'deleted');

        $response = $this->actingAs($user)->delete(route('activitylog.destroy', $log->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('activity_logs', ['id' => $log->id]);
    }

    public function test_it_can_filter_logs_by_user_id_and_name()
    {
        $user1 = WebTestUser::create([
            'name' => 'Alice Smith',
            'email' => 'alice@example.com',
            'role' => 'admin',
        ]);

        $user2 = WebTestUser::create([
            'name' => 'Bob Jones',
            'email' => 'bob@example.com',
            'role' => 'manager',
        ]);

        $this->actingAs($user1);
        ActivityLog::record(title: 'Alice action', module: 'User', action: 'created');

        $this->actingAs($user2);
        ActivityLog::record(title: 'Bob action', module: 'Course', action: 'updated');

        // Filter by user ID
        $response1 = $this->actingAs($user1)->get(route('activitylog.index', ['user' => $user1->id]));
        $response1->assertStatus(200);
        $response1->assertSee('Alice action');
        $response1->assertDontSee('Bob action');

        // Filter by user name string
        $response2 = $this->actingAs($user1)->get(route('activitylog.index', ['user' => 'Bob']));
        $response2->assertStatus(200);
        $response2->assertSee('Bob action');
        $response2->assertDontSee('Alice action');
    }

    public function test_it_populates_user_dropdown_options()
    {
        $user = WebTestUser::create([
            'name' => 'Charlie Brown',
            'email' => 'charlie@example.com',
            'role' => 'admin',
        ]);

        $this->actingAs($user);
        ActivityLog::record(title: 'Charlie logged in', module: 'Auth', action: 'login');

        $response = $this->actingAs($user)->get(route('activitylog.index'));

        $response->assertStatus(200);
        $response->assertSee('All Users');
        $response->assertSee('Charlie Brown');
    }
}
