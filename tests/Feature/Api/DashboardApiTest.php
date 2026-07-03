<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DashboardApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_read_the_dashboard(): void
    {
        $this->getJson('/api/v1/dashboard')->assertUnauthorized();
    }

    public function test_it_returns_the_client_home_summary(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/v1/dashboard')
            ->assertOk()
            ->assertJsonStructure([
                'stats' => [
                    'active_projects', 'pending_projects', 'completed_projects',
                    'requests_in_review', 'requests_approved',
                    'meetings_this_week', 'meetings_today',
                    'total_tokens', 'ai_assessments',
                ],
                'recent_activity',
                'active_projects',
            ])
            // A brand-new client has nothing yet: counters are zero, lists empty.
            ->assertJsonPath('stats.active_projects', 0)
            ->assertJsonPath('stats.completed_projects', 0)
            ->assertJsonPath('recent_activity', [])
            ->assertJsonPath('active_projects', []);
    }
}
