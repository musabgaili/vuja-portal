<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RequestsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_read_requests(): void
    {
        $this->getJson('/api/v1/requests')->assertUnauthorized();
    }

    public function test_a_client_gets_the_unified_feed_contract(): void
    {
        $client = User::factory()->create();
        Sanctum::actingAs($client);

        $this->getJson('/api/v1/requests')
            ->assertOk()
            ->assertJsonStructure([
                'items',
                'summary' => ['total', 'ideas', 'consultations', 'research', 'ip', 'copyright', 'threed', 'pending', 'in_progress', 'completed'],
                'pagination' => ['current_page', 'per_page', 'total', 'last_page', 'has_more'],
            ])
            ->assertJsonPath('summary.total', 0)
            ->assertJsonPath('pagination.current_page', 1)
            ->assertJsonCount(0, 'items');
    }

    public function test_internal_staff_cannot_read_client_requests(): void
    {
        $staff = User::factory()->create(['role' => 'employee']); // factory flips type -> internal

        Sanctum::actingAs($staff);

        $this->getJson('/api/v1/requests')->assertForbidden();
    }
}
