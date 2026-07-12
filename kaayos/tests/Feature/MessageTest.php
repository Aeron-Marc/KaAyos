<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Message;
use App\Models\User;
use App\Notifications\NewMessage;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class MessageTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    protected User $client;
    protected User $worker;
    protected Booking $booking;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = User::factory()->create(['role' => 'client']);
        $this->worker = User::factory()->create(['role' => 'worker']);

        $this->booking = Booking::create([
            'client_id'        => $this->client->id,
            'worker_id'        => $this->worker->id,
            'service_category' => 'Plumbing',
            'scheduled_at'     => now()->addDay(),
            'address'          => '123, Brgy. Bayanan, Tuy, Batangas',
            'house_no'         => '123',
            'barangay'         => 'Brgy. Bayanan',
            'status'           => Booking::STATUS_ACCEPTED,
        ]);
    }

    public function test_client_can_send_message_to_own_booking(): void
    {
        Notification::fake();

        $response = $this->actingAs($this->client)
            ->postJson(route('client.messages.send'), [
                'booking_id' => $this->booking->id,
                'message'    => 'Hello, are you available?',
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('messages', [
            'booking_id' => $this->booking->id,
            'sender_id'  => $this->client->id,
            'receiver_id' => $this->worker->id,
            'message'    => 'Hello, are you available?',
        ]);

        Notification::assertSentTo($this->worker, NewMessage::class);
    }

    public function test_worker_can_send_message_to_assigned_booking(): void
    {
        Notification::fake();

        $response = $this->actingAs($this->worker)
            ->postJson(route('worker.messages.send'), [
                'booking_id' => $this->booking->id,
                'message'    => 'Yes, I will come.',
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('messages', [
            'booking_id'  => $this->booking->id,
            'sender_id'   => $this->worker->id,
            'receiver_id' => $this->client->id,
            'message'     => 'Yes, I will come.',
        ]);

        Notification::assertSentTo($this->client, NewMessage::class);
    }

    public function test_client_cannot_send_message_to_others_booking(): void
    {
        $otherClient = User::factory()->create(['role' => 'client']);

        $response = $this->actingAs($otherClient)
            ->postJson(route('client.messages.send'), [
                'booking_id' => $this->booking->id,
                'message'    => 'Hello?',
            ]);

        $response->assertStatus(403);
    }

    public function test_worker_cannot_send_message_to_unassigned_booking(): void
    {
        $otherWorker = User::factory()->create(['role' => 'worker']);

        $response = $this->actingAs($otherWorker)
            ->postJson(route('worker.messages.send'), [
                'booking_id' => $this->booking->id,
                'message'    => 'Hello?',
            ]);

        $response->assertStatus(403);
    }

    public function test_poll_returns_messages_after_id(): void
    {
        $msg1 = Message::create([
            'booking_id'  => $this->booking->id,
            'sender_id'   => $this->client->id,
            'receiver_id' => $this->worker->id,
            'message'     => 'First message',
        ]);

        $msg2 = Message::create([
            'booking_id'  => $this->booking->id,
            'sender_id'   => $this->worker->id,
            'receiver_id' => $this->client->id,
            'message'     => 'Second message',
        ]);

        $response = $this->actingAs($this->client)
            ->getJson("/client/messages/poll/{$this->booking->id}?after={$msg1->id}");

        $response->assertOk()
            ->assertJsonCount(1, 'messages')
            ->assertJsonPath('messages.0.text', 'Second message');
    }

    public function test_mark_as_read_updates_read_at(): void
    {
        Message::create([
            'booking_id'  => $this->booking->id,
            'sender_id'   => $this->client->id,
            'receiver_id' => $this->worker->id,
            'message'     => 'Hello!',
        ]);

        $response = $this->actingAs($this->worker)
            ->postJson("/worker/messages/{$this->booking->id}/read");

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('messages', [
            'booking_id' => $this->booking->id,
            'sender_id'  => $this->client->id,
        ]);
    }

    public function test_poll_requires_auth(): void
    {
        $response = $this->getJson("/client/messages/poll/{$this->booking->id}");

        $response->assertUnauthorized();
    }

    public function test_send_requires_auth(): void
    {
        $response = $this->postJson(route('client.messages.send'), [
            'booking_id' => $this->booking->id,
            'message'    => 'Hi',
        ]);

        $response->assertUnauthorized();
    }
}
