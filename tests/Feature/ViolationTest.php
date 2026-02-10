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
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ViolationTest extends TestCase
{
    use RefreshDatabase;
    public function test_simple_user_cant_create_violation()
    {
        $this->seed(RoleSeeder::class);

        $user = User::factory()->create([
            'role_id' => 1
        ]);
        $this->actingAs($user);
        $response = $this->postJson('api/moderation/violation', [
            'user_id' => $user->id,
            'type' => 'spam',
            'severity' => 'major'
        ]);
        $response->assertStatus(403);
    }

    public function test_moderator_can_apply_violation_to_user_correctly(): void
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
            'severity' => 'major'
        ]);

        $this->assertDatabaseHas('violations', [
            'user_id' => $user->id,
            'moderator_id' => $moderator->id,
            'type' => 'spam',
            'severity' => 'major'
        ]);

        $this->assertDatabaseHas('reputations', [
            'user_id' => $user->id,
            'score' => 10,
            'level' => 'low'
        ]);


    }
}
