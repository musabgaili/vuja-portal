<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Guards the behaviour-preserving extraction of the client dashboard stats
 * into ClientDashboardService: the web dashboard must still render for a client.
 */
class ClientDashboardWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_client_can_load_the_web_dashboard(): void
    {
        $client = User::factory()->create(); // default: verified client

        $this->actingAs($client)
            ->get(route('client.dashboard'))
            ->assertOk();
    }
}
