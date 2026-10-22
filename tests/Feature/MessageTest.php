<?php

namespace Tests\Feature;

use App\Models\Message;
use App\Models\User;
use App\Notifications\NewMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_two_users_can_exchange_messages_directly_without_a_booking(): void
    {
        $sender = User::factory()->create();
        $traveler = User::factory()->create();

        Sanctum::actingAs($sender);
        $this->postJson("/api/v1/users/{$traveler->id}/messages", ['body' => 'Hi, still available?'])
            ->assertCreated()
            ->assertJsonPath('data.is_mine', true);

        Sanctum::actingAs($traveler);
        $response = $this->getJson("/api/v1/users/{$sender->id}/messages");
        $response->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.body', 'Hi, still available?')
            ->assertJsonPath('data.0.is_mine', false);
    }

    public function test_sending_a_message_notifies_the_receiver(): void
    {
        Notification::fake();

        $sender = User::factory()->create();
        $receiver = User::factory()->create();

        Sanctum::actingAs($sender);
        $this->postJson("/api/v1/users/{$receiver->id}/messages", ['body' => 'Hi, still available?'])->assertCreated();

        Notification::assertSentTo($receiver, NewMessage::class);
    }

    public function test_reading_messages_marks_them_read(): void
    {
        $sender = User::factory()->create();
        $traveler = User::factory()->create();

        Sanctum::actingAs($sender);
        $this->postJson("/api/v1/users/{$traveler->id}/messages", ['body' => 'Hello'])->assertCreated();

        Sanctum::actingAs($traveler);
        $this->getJson("/api/v1/users/{$sender->id}/messages")->assertOk();

        $this->assertNotNull(Message::first()->read_at);
    }

    public function test_cannot_message_yourself(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);
        $this->postJson("/api/v1/users/{$user->id}/messages", ['body' => 'Hey'])->assertUnprocessable();
    }

    public function test_blocked_users_cannot_message_each_other(): void
    {
        $blocker = User::factory()->create();
        $blocked = User::factory()->create();

        Sanctum::actingAs($blocker);
        $this->postJson("/api/v1/users/{$blocked->id}/block")->assertCreated();
        $this->postJson("/api/v1/users/{$blocked->id}/messages", ['body' => 'Hey'])->assertUnprocessable();

        Sanctum::actingAs($blocked);
        $this->postJson("/api/v1/users/{$blocker->id}/messages", ['body' => 'Hey'])->assertUnprocessable();
    }

    public function test_conversations_list_shows_the_other_party_last_message_and_unread_count(): void
    {
        $me = User::factory()->create();
        $alice = User::factory()->create(['name' => 'Alice']);
        $bob = User::factory()->create(['name' => 'Bob']);

        Sanctum::actingAs($alice);
        $this->postJson("/api/v1/users/{$me->id}/messages", ['body' => 'Hi from Alice'])->assertCreated();

        Sanctum::actingAs($bob);
        $this->postJson("/api/v1/users/{$me->id}/messages", ['body' => 'Hi from Bob'])->assertCreated();
        $this->postJson("/api/v1/users/{$me->id}/messages", ['body' => 'Second message from Bob'])->assertCreated();

        Sanctum::actingAs($me);
        $response = $this->getJson('/api/v1/conversations');

        $response->assertOk()->assertJsonCount(2, 'data');
        $bobConversation = collect($response->json('data'))->firstWhere('other_user.id', $bob->id);
        $this->assertSame('Second message from Bob', $bobConversation['last_message']['body']);
        $this->assertSame(2, $bobConversation['unread_count']);

        $this->getJson('/api/v1/messages/unread-count')->assertOk()->assertJsonPath('unread_count', 3);
    }
}
