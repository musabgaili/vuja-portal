<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Guards the behaviour-preserving extraction of the "My Requests" aggregation
 * into ClientRequestsService: the web page must still render for a client.
 */
class ClientRequestsWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_client_can_load_the_web_my_requests_page(): void
    {
        $client = User::factory()->create(); // default: verified client

        $this->actingAs($client)
            ->get(route('client.requests'))
            ->assertOk();
    }
}
