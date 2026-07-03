<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProfileApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_read_the_profile(): void
    {
        $this->getJson('/api/v1/profile')->assertUnauthorized();
    }

    public function test_it_returns_the_authenticated_users_profile(): void
    {
        $user = User::factory()->create(['email' => 'client@example.com']);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/profile')
            ->assertOk()
            ->assertJsonPath('email', 'client@example.com')
            ->assertJsonPath('id', $user->id);
    }

    public function test_it_updates_name_and_phone(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->putJson('/api/v1/profile', ['name' => 'New Name', 'phone' => '0551234567'])
            ->assertOk()
            ->assertJsonPath('name', 'New Name')
            ->assertJsonPath('phone', '0551234567');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Name',
            'phone' => '0551234567',
        ]);
    }

    public function test_it_rejects_a_non_numeric_phone(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->putJson('/api/v1/profile', ['name' => 'Ok', 'phone' => 'abc-123'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    }

    public function test_it_changes_the_password_with_the_correct_current_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('password')]);
        Sanctum::actingAs($user);

        $this->putJson('/api/v1/profile/password', [
            'current_password' => 'password',
            'password' => 'new-secret-123',
            'password_confirmation' => 'new-secret-123',
        ])->assertOk();

        $this->assertTrue(Hash::check('new-secret-123', $user->fresh()->password));
    }

    public function test_it_rejects_a_password_change_with_a_wrong_current_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('password')]);
        Sanctum::actingAs($user);

        $this->putJson('/api/v1/profile/password', [
            'current_password' => 'wrong-password',
            'password' => 'new-secret-123',
            'password_confirmation' => 'new-secret-123',
        ])->assertStatus(422)->assertJsonValidationErrors(['current_password']);

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_it_changes_the_email_and_requires_reverification(): void
    {
        $user = User::factory()->create([
            'email' => 'old@example.com',
            'password' => Hash::make('password'),
        ]);
        Sanctum::actingAs($user);

        $this->putJson('/api/v1/profile/email', [
            'email' => 'new@example.com',
            'current_password' => 'password',
        ])->assertOk()->assertJsonPath('user.email', 'new@example.com');

        $fresh = $user->fresh();
        $this->assertSame('new@example.com', $fresh->email);
        $this->assertNull($fresh->email_verified_at);
    }
}
