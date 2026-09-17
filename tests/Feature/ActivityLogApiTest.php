<?php

namespace Rahad\ActivityLog\Tests\Feature;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Rahad\ActivityLog\Models\ActivityLog;
use Rahad\ActivityLog\Tests\TestCase;

class ApiTestUser extends Authenticatable
{
    protected $table = 'users';
    protected $guarded = [];
}

class ActivityLogApiTest extends TestCase
{
    public function test_it_can_list_activity_logs_via_api()
    {
        $user = ApiTestUser::create([
            'name' => 'API Admin',
            'email' => 'apiadmin@example.com',
            'role' => 'admin',
        ]);

        ActivityLog::record(title: 'API Log 1', module: 'Auth', action: 'login');
        ActivityLog::record(title: 'API Log 2', module: 'Course', action: 'created');

        $response = $this->actingAs($user)->getJson(route('api.activitylog.index'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => ['id', 'title', 'module', 'action'],
                ],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);
    }

    public function test_it_can_get_date_groups_via_api()
    {
        $user = ApiTestUser::create([
            'name' => 'API Admin',
            'email' => 'apiadmin2@example.com',
            'role' => 'admin',
        ]);

        ActivityLog::record(title: 'API Log 1', module: 'Auth', action: 'login');

        $response = $this->actingAs($user)->getJson(route('api.activitylog.date-groups'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => ['date', 'label', 'count'],
                ],
            ]);
    }

    public function test_it_can_bulk_delete_via_api()
    {
        $user = ApiTestUser::create([
            'name' => 'API Admin',
            'email' => 'apiadmin3@example.com',
            'role' => 'admin',
        ]);

        $log1 = ActivityLog::record(title: 'API Log 1', module: 'Auth', action: 'login');
        $log2 = ActivityLog::record(title: 'API Log 2', module: 'Auth', action: 'logout');

        $response = $this->actingAs($user)->postJson(route('api.activitylog.bulk-destroy'), [
            'ids' => [$log1->id, $log2->id],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'deleted_count' => 2,
                ],
            ]);

        $this->assertDatabaseMissing('activity_logs', ['id' => $log1->id]);
        $this->assertDatabaseMissing('activity_logs', ['id' => $log2->id]);
    }
}
