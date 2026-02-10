<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;
    public function test_user_cant_send_report_to_same_user_without_24h_delay(): void
    {
        $this->seed(RoleSeeder::class);

        $user1 = User::factory()->create([
            'role_id' => 1
        ]);

        $user2 = User::factory()->create([
            'role_id' => 1
        ]);
        $this->actingAs($user1);

        $response1 = $this->postJson('api/report', ['target_name' => $user2->name, 'reason' => 'testtest1233123']);
        $response1->assertStatus(200);
        $response2 = $this->postJson('api/report', ['target_name' => $user2->name, 'reason' => 'test']);
        $response2->assertStatus(403);
        $this->assertDatabaseMissing('reports', ['reporter_id' => $user1->id, 'target_user_id' => $user2->id, 'reason' => 'test']);
    }

    public function test_user_can_send_report(): void
    {
        $this->seed(RoleSeeder::class);

        $user1 = User::factory()->create([
            'role_id' => 1
        ]);

        $user2 = User::factory()->create([
            'role_id' => 1
        ]);
        $this->actingAs($user1);

        $response = $this->postJson('api/report', ['target_name' => $user2->name, 'reason' => 'testtest1233123']);
        $response->assertStatus(200);
        $this->assertDatabaseHas('reports', ['reporter_id' => $user1->id, 'target_user_id' => $user2->id, 'reason' => 'testtest1233123']);
    }

    public function test_moderator_can_change_report_status()
    {
        $this->seed(RoleSeeder::class);

        $user1 = User::factory()->create([
            'role_id' => 1
        ]);
        $user2 = User::factory()->create([
            'role_id' => 1
        ]);
        $this->actingAs($user1);
        $response = $this->postJson('api/report', ['target_name' => $user2->name, 'reason' => 'faposD&Sr473NDSF97']);
        $response->assertStatus(200);
        $report = Report::where('reason', 'faposD&Sr473NDSF97')->first();
        $this->assertNotNull($report);


        $moderator = User::factory()->create([
            'role_id' => 2
        ]);
        $this->actingAs($moderator);

        $response = $this->postJson('api/moderation/change_report_status', [
            'report_id' => $report->id,
            'status' => 'rejected'
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('reports', ['reporter_id' => $user1->id, 'target_user_id' => $user2->id, 'reason' => 'faposD&Sr473NDSF97', 'status' => 'rejected']);
    }
}
