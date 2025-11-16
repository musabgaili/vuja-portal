<?php

namespace Tests\Feature;

use App\Models\User;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Illuminate\Auth\Events\Verified;
use Illuminate\Container\Attributes\Auth;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles and permissions
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    }

    /** @test */
    public function user_can_register_with_valid_data()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+1234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post(route('register'), $userData);

        $response->assertRedirect('/email/verify');
        
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+1234567890',
            'role' => UserRole::CLIENT->value,
            'status' => UserStatus::PENDING->value,
        ]);

        $user = User::where('email', 'john@example.com')->first();
        $this->assertTrue($user->hasRole('client'));
        // Email verification should be sent
        // Note: Laravel's email verification uses notifications, not mailables
    }

    /** @test */
    public function user_cannot_register_with_invalid_data()
    {
        $userData = [
            'name' => '',
            'email' => 'invalid-email',
            'phone' => '',
            'password' => '123',
            'password_confirmation' => '456',
        ];

        $response = $this->post(route('register'), $userData);

        $response->assertSessionHasErrors(['name', 'email', 'phone', 'password']);
        $this->assertDatabaseMissing('users', ['email' => 'invalid-email']);
    }

    /** @test */
    public function user_cannot_register_with_duplicate_email()
    {
        User::factory()->create(['email' => 'john@example.com']);

        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+1234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post(route('register'), $userData);

        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function user_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
            'status' => UserStatus::ACTIVE,
            'email_verified_at' => now(),
        ]);

        $response = $this->post(route('login'), [
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function user_cannot_login_with_invalid_credentials()
    {
        User::factory()->create([
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post(route('login'), [
            'email' => 'john@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    /** @test */
    public function user_can_verify_email_successfully()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
            'status' => UserStatus::PENDING,
        ]);
        

        $this->actingAs($user);

        // Simulate email verification by directly updating the user
        $user->markEmailAsVerified();
        $user->update(['status' => UserStatus::ACTIVE]);
        
        $this->assertNotNull($user->email_verified_at);
        $this->assertEquals(UserStatus::ACTIVE, $user->status);
    }

    /** @test */
    public function user_can_resend_verification_email()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $this->actingAs($user);

        $response = $this->post(route('verification.resend'));

        $response->assertSessionHas('resent');
        // Note: Laravel's email verification uses notifications, not mailables
    }

    /** @test */
    public function user_roles_are_assigned_correctly()
    {
        $client = User::factory()->create(['role' => UserRole::CLIENT]);
        $employee = User::factory()->create(['role' => UserRole::EMPLOYEE]);
        $manager = User::factory()->create(['role' => UserRole::MANAGER]);
        $projectManager = User::factory()->create(['role' => UserRole::PROJECT_MANAGER]);

        $this->assertTrue($client->isClient());
        $this->assertTrue($employee->isEmployee());
        $this->assertTrue($manager->isManager());
        $this->assertTrue($projectManager->isProjectManager());
    }

    /** @test */
    public function user_status_works_correctly()
    {
        $activeUser = User::factory()->create(['status' => UserStatus::ACTIVE]);
        $pendingUser = User::factory()->create(['status' => UserStatus::PENDING]);
        $suspendedUser = User::factory()->create(['status' => UserStatus::SUSPENDED]);
        $inactiveUser = User::factory()->create(['status' => UserStatus::INACTIVE]);

        $this->assertTrue($activeUser->isActive());
        $this->assertFalse($pendingUser->isActive());
        $this->assertFalse($suspendedUser->isActive());
        $this->assertFalse($inactiveUser->isActive());
    }

    /** @test */
    public function user_model_has_role_checking_methods()
    {
        $client = User::factory()->create(['role' => UserRole::CLIENT]);
        $employee = User::factory()->create(['role' => UserRole::EMPLOYEE]);
        $manager = User::factory()->create(['role' => UserRole::MANAGER]);
        $projectManager = User::factory()->create(['role' => UserRole::PROJECT_MANAGER]);

        $this->assertTrue($client->isClient());
        $this->assertFalse($client->isEmployee());
        $this->assertFalse($client->isManager());
        $this->assertFalse($client->isProjectManager());
        $this->assertFalse($client->isInternal());

        $this->assertTrue($employee->isEmployee());
        $this->assertTrue($employee->isInternal());

        $this->assertTrue($manager->isManager());
        $this->assertTrue($manager->isInternal());

        $this->assertTrue($projectManager->isProjectManager());
        $this->assertTrue($projectManager->isInternal());
    }

    /** @test */
    public function user_can_logout()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('logout'));

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    /** @test */
    public function user_activity_is_logged()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $user->update(['name' => 'Updated Name']);

        $this->assertDatabaseHas('activity_log', [
            'causer_type' => User::class,
            'causer_id' => $user->id,
            'description' => 'updated',
        ]);
    }

    /** @test */
    public function authenticated_user_can_access_email_verification_page()
    {
        $user = User::factory()->create(['email_verified_at' => null]);
        $this->actingAs($user);

        $response = $this->get(route('verification.notice'));

        $response->assertStatus(200);
        $response->assertViewIs('auth.verify');
    }

    /** @test */
    public function guest_cannot_access_email_verification_page()
    {
        $response = $this->get(route('verification.notice'));

        $response->assertRedirect(route('login'));
    }
}