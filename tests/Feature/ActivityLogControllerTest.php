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
}
