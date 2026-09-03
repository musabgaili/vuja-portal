<?php

namespace Tests\Feature\Api\V1;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ChatApiTest extends TestCase
{
    use RefreshDatabase;

    private function internal(string $name = 'Alice'): User
    {
        return User::factory()->create([
            'name' => $name,
            'role' => UserRole::EMPLOYEE,
            'type' => 'internal',
            'status' => UserStatus::ACTIVE,
        ]);
    }

    public function test_list_channels_and_send_message(): void
    {
        $alice = $this->internal('Alice');
        $bob = $this->internal('Bob');

        $channel = app(ChatService::class)->createChannel($alice, 'engineering', null, [$bob->id], false);

        Sanctum::actingAs($alice);

        $this->getJson('/api/v1/chat/channels')
            ->assertOk()
            ->assertJsonPath('0.name', 'engineering');

        $this->postJson('/api/v1/chat/channels/'.$channel->id.'/messages', [
            'body' => 'Hello team',
        ])
            ->assertCreated()
            ->assertJsonPath('message.body', 'Hello team');

        $this->getJson('/api/v1/chat/channels/'.$channel->id.'/messages')
            ->assertOk()
            ->assertJsonCount(1, 'items');
    }

    public function test_dm_between_two_users(): void
    {
        $alice = $this->internal('Alice');
        $bob = $this->internal('Bob');

        Sanctum::actingAs($alice);

        $this->postJson('/api/v1/chat/dm', ['members' => [$bob->id]])
            ->assertCreated()
            ->assertJsonPath('channel.is_dm', true);
    }

    public function test_react_to_message(): void
    {
        $user = $this->internal();
        $channel = app(ChatService::class)->createChannel($user, 'general', null, [], false);
        $message = app(ChatService::class)->send($channel, $user, 'Nice work');

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/chat/messages/'.$message->id.'/react', ['emoji' => '👍'])
            ->assertOk()
            ->assertJsonPath('message.reactions.0.emoji', '👍');
    }

    public function test_edit_own_message(): void
    {
        $user = $this->internal();
        $channel = app(ChatService::class)->createChannel($user, 'general', null, [], false);
        $message = app(ChatService::class)->send($channel, $user, 'Draft');

        Sanctum::actingAs($user);

        $this->patchJson('/api/v1/chat/messages/'.$message->id, ['body' => 'Final'])
            ->assertOk()
            ->assertJsonPath('message.body', 'Final')
            ->assertJsonPath('message.edited', true);
    }
}
