<?php

namespace Tests\Feature\Api\V1;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\FcmToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    private function staff(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'role' => UserRole::EMPLOYEE,
            'type' => 'internal',
            'status' => UserStatus::ACTIVE,
            'email' => 'employee@vujade.test',
            'password' => 'password',
        ], $overrides));
    }

    public function test_login_returns_bearer_token_and_role_flags(): void
    {
        $user = $this->staff();

        $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'password',
            'device_name' => 'Pixel 8',
        ])
            ->assertOk()
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('user.email', $user->email)
            ->assertJsonPath('user.is_internal', true)
            ->assertJsonPath('user.is_manager', false)
            ->assertJsonPath('user.role', 'employee')
            ->assertJsonStructure(['token', 'token_type', 'expires_at', 'user' => ['id', 'locale']]);
    }

    public function test_login_rejects_bad_password_without_enumerating_users(): void
    {
        $this->staff();

        $this->postJson('/api/v1/login', [
            'email' => 'nobody@vujade.test',
            'password' => 'wrong',
        ])->assertStatus(422);

        $this->postJson('/api/v1/login', [
            'email' => 'employee@vujade.test',
            'password' => 'wrong',
        ])->assertStatus(422);
    }

    public function test_login_rejects_suspended_accounts(): void
    {
        $this->staff(['status' => UserStatus::SUSPENDED]);

        $this->postJson('/api/v1/login', [
            'email' => 'employee@vujade.test',
            'password' => 'password',
        ])->assertStatus(422);
    }

    public function test_me_requires_token_and_returns_profile(): void
    {
        $user = $this->staff();

        $this->getJson('/api/v1/me')->assertUnauthorized();

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('email', $user->email)
            ->assertJsonPath('is_internal', true);
    }

    public function test_patch_me_updates_name_phone_and_locale(): void
    {
        $user = $this->staff();
        Sanctum::actingAs($user);

        $this->patchJson('/api/v1/me', [
            'name' => 'Updated Employee',
            'phone' => '966500000000',
            'locale' => 'ar',
        ])
            ->assertOk()
            ->assertJsonPath('name', 'Updated Employee')
            ->assertJsonPath('locale', 'ar');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Employee',
            'locale' => 'ar',
        ]);
    }

    public function test_password_change_and_optional_revoke_other_devices(): void
    {
        $user = $this->staff();
        $keep = $user->createToken('keep')->plainTextToken;
        $other = $user->createToken('other');

        $this->withToken($keep)->putJson('/api/v1/me/password', [
            'current_password' => 'password',
            'password' => 'Vuja-Test-Pass-2026!',
            'password_confirmation' => 'Vuja-Test-Pass-2026!',
            'revoke_other_devices' => true,
        ])->assertOk();

        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $other->accessToken->id]);
        $this->assertDatabaseHas('personal_access_tokens', ['name' => 'keep']);

        $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'Vuja-Test-Pass-2026!',
        ])->assertOk();
    }

    public function test_logout_revokes_only_current_token_and_optional_fcm(): void
    {
        $user = $this->staff();
        $a = $user->createToken('a')->plainTextToken;
        $b = $user->createToken('b')->plainTextToken;
        FcmToken::factory()->create(['user_id' => $user->id, 'token' => 'fcm-device-a']);

        $this->withToken($a)->postJson('/api/v1/logout', ['fcm_token' => 'fcm-device-a'])
            ->assertOk();

        $this->app['auth']->forgetGuards();

        $this->withToken($a)->getJson('/api/v1/me')->assertUnauthorized();
        $this->withToken($b)->getJson('/api/v1/me')->assertOk();
        $this->assertDatabaseMissing('fcm_tokens', ['token' => 'fcm-device-a']);
    }

    public function test_logout_all_revokes_every_token_and_fcm(): void
    {
        $user = $this->staff();
        $a = $user->createToken('a')->plainTextToken;
        $user->createToken('b');
        FcmToken::factory()->create(['user_id' => $user->id]);

        $this->withToken($a)->postJson('/api/v1/logout-all')->assertOk();

        $this->assertSame(0, $user->tokens()->count());
        $this->assertSame(0, $user->fcmTokens()->count());
    }
}
