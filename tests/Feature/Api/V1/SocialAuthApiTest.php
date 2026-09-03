<?php

namespace Tests\Feature\Api\V1;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use App\Services\Auth\AppleTokenVerifier;
use App\Support\Auth\SocialIdentity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SocialAuthApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        config([
            'services.google.client_id' => 'web-client.apps.googleusercontent.com',
            'services.google.client_ids' => ['web-client.apps.googleusercontent.com', 'android-client.apps.googleusercontent.com'],
            'services.apple.client_id' => 'com.vujade.portal',
            'services.apple.client_ids' => ['com.vujade.portal'],
            'mobile.social_auto_register' => false,
        ]);
    }

    public function test_google_login_links_existing_verified_staff_by_email(): void
    {
        $user = User::factory()->create([
            'email' => 'pm@vujade.test',
            'role' => UserRole::PROJECT_MANAGER,
            'type' => 'internal',
            'status' => UserStatus::ACTIVE,
            'email_verified_at' => now(),
        ]);

        Http::fake([
            'oauth2.googleapis.com/tokeninfo*' => Http::response([
                'aud' => 'android-client.apps.googleusercontent.com',
                'sub' => 'google-sub-99',
                'email' => 'pm@vujade.test',
                'email_verified' => 'true',
                'name' => 'Project Manager',
            ]),
        ]);

        $this->postJson('/api/v1/auth/google', [
            'id_token' => 'fake-google-id-token',
            'device_name' => 'iPhone',
        ])
            ->assertOk()
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonPath('user.role', 'project_manager')
            ->assertJsonPath('user.provider', 'google');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'provider' => 'google',
            'provider_id' => 'google-sub-99',
        ]);
    }

    public function test_google_login_does_not_auto_create_staff_when_unknown(): void
    {
        Http::fake([
            'oauth2.googleapis.com/tokeninfo*' => Http::response([
                'aud' => 'web-client.apps.googleusercontent.com',
                'sub' => 'google-new',
                'email' => 'unknown@vujade.test',
                'email_verified' => 'true',
                'name' => 'Unknown',
            ]),
        ]);

        $this->postJson('/api/v1/auth/google', [
            'id_token' => 'fake-google-id-token',
        ])
            ->assertStatus(422)
            ->assertJsonFragment(['No account found for this login. Ask a manager to invite you.']);

        $this->assertDatabaseMissing('users', ['email' => 'unknown@vujade.test']);
    }

    public function test_google_login_rejects_wrong_audience(): void
    {
        Http::fake([
            'oauth2.googleapis.com/tokeninfo*' => Http::response([
                'aud' => 'attacker-app.apps.googleusercontent.com',
                'sub' => 'google-sub',
                'email' => 'pm@vujade.test',
                'email_verified' => 'true',
            ]),
        ]);

        $this->postJson('/api/v1/auth/google', [
            'id_token' => 'fake-google-id-token',
        ])->assertStatus(422);
    }

    public function test_google_can_auto_register_client_when_enabled(): void
    {
        config(['mobile.social_auto_register' => true]);

        Http::fake([
            'oauth2.googleapis.com/tokeninfo*' => Http::response([
                'aud' => 'web-client.apps.googleusercontent.com',
                'sub' => 'google-client-1',
                'email' => 'newclient@example.test',
                'email_verified' => 'true',
                'name' => 'New Client',
            ]),
        ]);

        $this->postJson('/api/v1/auth/google', [
            'id_token' => 'fake-google-id-token',
        ])
            ->assertOk()
            ->assertJsonPath('user.role', 'client')
            ->assertJsonPath('user.email', 'newclient@example.test');
    }

    public function test_apple_login_uses_existing_provider_id(): void
    {
        $user = User::factory()->create([
            'email' => 'manager@vujade.test',
            'role' => UserRole::MANAGER,
            'type' => 'internal',
            'status' => UserStatus::ACTIVE,
            'provider' => 'apple',
            'provider_id' => 'apple-sub-1',
        ]);

        $this->mock(AppleTokenVerifier::class, function ($mock) {
            $mock->shouldReceive('verify')->once()->andReturn(new SocialIdentity(
                provider: 'apple',
                providerId: 'apple-sub-1',
                email: 'manager@vujade.test',
                name: 'Manager',
                emailVerified: true,
            ));
        });

        $this->postJson('/api/v1/auth/apple', [
            'identity_token' => 'fake-apple-identity-token',
            'nonce' => 'abc',
            'full_name' => 'Manager Name',
        ])
            ->assertOk()
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonPath('user.is_manager', true);
    }
}
