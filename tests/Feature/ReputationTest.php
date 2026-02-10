<?php

namespace Tests\Feature;

use App\Enums\PenaltyType;
use App\Enums\UserStatus;
use App\Models\Reputation;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ReputationTest extends TestCase
{
    use RefreshDatabase;


    public function test_user_will_be_timeouted_when_reputation_bellow_zero_correctly()
    {
        $this->seed(RoleSeeder::class);
        $moderator = User::factory()->create([
            'role_id' => 2
        ]);

        $user = User::factory()->create();

        Reputation::create([
            'user_id' => $user->id,
            'score' => 30,
            'level' => 'medium'
        ]);
        $this->actingAs($moderator);

        $this->postJson('api/moderation/violation', [
            'user_id' => $user->id,
            'type' => 'spam',
            'severity' => 'critical'
        ]);

        $this->assertDatabaseHas(
            'users',
            [
                'id' => $user->id,
                'status' => UserStatus::TIMEOUT
            ]
        );
        $this->assertDatabaseHas('penalties', [
            'user_id' => $user->id,
            'type' => PenaltyType::TEMP_BLOCK
        ]);
    }

    public function test_user_will_be_banned_when_reputation_bellow_ban_border_correctly()
    {
        $this->seed(RoleSeeder::class);
        $moderator = User::factory()->create([
            'role_id' => 2
        ]);

        $user = User::factory()->create();

        Reputation::create([
            'user_id' => $user->id,
            'score' => 30,
            'level' => 'medium'
        ]);
        $this->actingAs($moderator);

        $this->postJson('api/moderation/violation', [
            'user_id' => $user->id,
            'type' => 'spam',
            'severity' => 'critical'
        ]);
        $this->postJson('api/moderation/violation', [
            'user_id' => $user->id,
            'type' => 'spam',
            'severity' => 'critical'
        ]);

        $this->assertDatabaseHas(
            'users',
            [
                'id' => $user->id,
                'status' => UserStatus::BANNED
            ]
        );
        $this->assertDatabaseHas('penalties', [
            'user_id' => $user->id,
            'type' => PenaltyType::PERM_BLOCK
        ]);
    }
}
