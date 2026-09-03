<?php

namespace Tests\Feature\Api\V1;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FcmTokenApiTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::factory()->create([
            'role' => UserRole::EMPLOYEE,
            'type' => 'internal',
            'status' => UserStatus::ACTIVE,
        ]);
    }

    public function test_register_and_refresh_fcm_token(): void
    {
        $user = $this->user();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/me/fcm-token', [
            'token' => 'fcm-abc',
            'device' => 'Pixel 8',
            'platform' => 'android',
        ])
            ->assertCreated()
            ->assertJsonPath('token', 'fcm-abc')
            ->assertJsonPath('platform', 'android');

        $this->postJson('/api/v1/me/fcm-token', [
            'token' => 'fcm-abc',
            'device' => 'Pixel 8a',
            'platform' => 'android',
        ])->assertCreated();

        $this->assertDatabaseCount('fcm_tokens', 1);
        $this->assertDatabaseHas('fcm_tokens', [
            'user_id' => $user->id,
            'device' => 'Pixel 8a',
        ]);
    }

    public function test_token_moves_to_the_user_who_logs_in_on_that_device(): void
    {
        $alice = $this->user();
        $bob = User::factory()->create([
            'role' => UserRole::MANAGER,
            'type' => 'internal',
            'status' => UserStatus::ACTIVE,
        ]);

        Sanctum::actingAs($alice);
        $this->postJson('/api/v1/me/fcm-token', [
            'token' => 'shared-device',
            'platform' => 'ios',
        ])->assertCreated();

        Sanctum::actingAs($bob);
        $this->postJson('/api/v1/me/fcm-token', [
            'token' => 'shared-device',
            'platform' => 'ios',
        ])->assertCreated();

        $this->assertDatabaseMissing('fcm_tokens', ['user_id' => $alice->id, 'token' => 'shared-device']);
        $this->assertDatabaseHas('fcm_tokens', ['user_id' => $bob->id, 'token' => 'shared-device']);
    }

    public function test_unregister_fcm_token(): void
    {
        $user = $this->user();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/me/fcm-token', ['token' => 'gone', 'platform' => 'web'])->assertCreated();

        $this->deleteJson('/api/v1/me/fcm-token', ['token' => 'gone'])
            ->assertOk();

        $this->assertDatabaseMissing('fcm_tokens', ['token' => 'gone']);
    }
}
