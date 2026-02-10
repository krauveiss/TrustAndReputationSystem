<?php

namespace Tests\Feature;

use App\Enums\PenaltyType;
use App\Enums\UserStatus;
use App\Models\Reputation;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_set_reputation_correctly(): void
    {
        $this->seed(RoleSeeder::class);
        $admin = User::factory()->create([
            'role_id' => 3
        ]);

        $user = User::factory()->create();

        Reputation::create([
            'user_id' => $user->id,
            'score' => 30,
            'level' => 'medium'
        ]);

        $this->actingAs($admin);
        $this->postJson('api/admin/set_reputation', [
            'user_id' => $user->id,
            'reputation' => 9999
        ]);

        $this->assertDatabaseHas('reputations', [
            'user_id' => $user->id,
            'score' => 9999,
            'level' => 'high'
        ]);

        $this->assertDatabaseHas('admin_logs', [
            'executive_id' => $admin->id,
            'action' => 'set reputation',
            'comment' => 'new reputation: 9999'
        ]);
    }

    public function test_admin_can_change_violation_status_correctly(): void
    {
        $this->seed(RoleSeeder::class);
        $admin = User::factory()->create([
            'role_id' => 3
        ]);

        $user = User::factory()->create();

        Reputation::create([
            'user_id' => $user->id,
            'score' => 30,
            'level' => 'medium'
        ]);

        $this->actingAs($admin);

        $response = $this->postJson('api/moderation/violation', [
            'user_id' => $user->id,
            'type' => 'spam',
            'severity' => 'minor'
        ]);
        $response->assertOk();

        $response1 = $this->postJson('api/admin/change_violation_status', [
            'violation_id' => 1,
            'status' => 'canceled',
            'comment' => 'test'
        ]);
        $response1->assertOk();
        $this->assertDatabaseHas('violations', ['id' => 1, 'status' => 'canceled', 'comment' => 'test']);
    }


    public function test_admin_can_ban_and_unban_users()
    {
        $this->seed(RoleSeeder::class);
        $admin = User::factory()->create([
            'role_id' => 3
        ]);
        $user = User::factory()->create();
        $this->actingAs($admin);

        $response = $this->postJson('api/admin/ban', [
            'user_id' => $user->id,
        ]);
        $response->assertOk();

        $this->assertDatabaseHas(
            'users',
            [
                'id' => $user->id,
                'status' => UserStatus::BANNED
            ]
        );
        $this->assertDatabaseHas('penalties', [
            'user_id' => $user->id,
            'initiator' => $admin->id,
            'type' => PenaltyType::PERM_BLOCK
        ]);

        $response = $this->postJson('api/admin/unban', [
            'user_id' => $user->id,
        ]);
        $response->assertOk();
        $this->assertDatabaseHas(
            'users',
            [
                'id' => $user->id,
                'status' => UserStatus::ACTIVE
            ]
        );

        $this->assertDatabaseHas('penalties', [
            'user_id' => $user->id,
            'initiator' => $admin->id,
            'type' => PenaltyType::UNBAN
        ]);
    }

    public function test_admin_can_untimeout_users()
    {
        $this->seed(RoleSeeder::class);
        $admin = User::factory()->create([
            'role_id' => 3
        ]);

        $user = User::factory()->create();
        Reputation::create([
            'user_id' => $user->id,
            'score' => 30,
            'level' => 'medium'
        ]);


        $this->actingAs($admin);

        $response = $this->postJson('api/moderation/violation', [
            'user_id' => $user->id,
            'type' => 'spam',
            'severity' => 'critical'
        ]);
        $response->assertOk();



        $response = $this->postJson('api/admin/untimeout', ['user_id' => $user->id]);
        $response->assertOk();
        $this->assertDatabaseHas(
            'users',
            [
                'id' => $user->id,
                'status' => UserStatus::ACTIVE
            ]
        );
        $this->assertDatabaseHas('penalties', [
            'user_id' => $user->id,
            'initiator' => $admin->id,
            'type' => PenaltyType::UNTIMEOUT
        ]);
    }

    public function test_admin_can_change_user_role()
    {
        $this->seed(RoleSeeder::class);
        $admin = User::factory()->create([
            'role_id' => 3
        ]);

        $user = User::factory()->create();
        $this->actingAs($admin);

        $response = $this->postJson('api/admin/change_user_role', ['user_id' => $user->id, 'role' => 2]);
        $response->assertOk();

        $this->assertDatabaseHas(
            'users',
            [
                'id' => $user->id,
                'role_id' => 2
            ]
        );

        $this->assertDatabaseHas('admin_logs', [
            'executive_id' => $admin->id,
            'action' => 'changed role'
        ]);
    }
}
