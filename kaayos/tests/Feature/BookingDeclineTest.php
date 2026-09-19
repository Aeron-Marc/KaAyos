<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use App\Notifications\BookingStatusChanged;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class BookingDeclineTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    protected User $client;
    protected User $worker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = User::factory()->create(['role' => 'client']);
        $this->worker = User::factory()->create(['role' => 'worker']);
    }

    protected function createBooking(string $status = Booking::STATUS_NEW): Booking
    {
        return Booking::create([
            'client_id'        => $this->client->id,
            'worker_id'        => $this->worker->id,
            'service_category' => 'Plumbing',
            'scheduled_at'     => now()->addDay(),
            'address'          => '123, Brgy. Bayanan, Tuy, Batangas',
            'house_no'         => '123',
            'barangay'         => 'Brgy. Bayanan',
            'status'           => $status,
            'price'            => 500.00,
        ]);
    }

    // --- Auth guard ---

    public function test_guest_cannot_decline_booking(): void
    {
        $booking = $this->createBooking();

        $response = $this->postJson("/worker/jobs/{$booking->id}/decline");

        $response->assertUnauthorized();
    }

    // --- Worker decline ---

    public function test_worker_can_decline_new_booking(): void
    {
        Notification::fake();

        $booking = $this->createBooking(Booking::STATUS_NEW);

        $response = $this->actingAs($this->worker)
            ->postJson("/worker/jobs/{$booking->id}/decline", [
                'reason' => 'Schedule conflict.',
            ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Job declined.');

        $this->assertDatabaseHas('bookings', [
            'id'     => $booking->id,
            'status' => Booking::STATUS_DECLINED,
        ]);

        $this->assertDatabaseHas('bookings', [
            'id'            => $booking->id,
            'decline_reason' => 'Schedule conflict.',
        ]);

        Notification::assertSentTo($this->client, BookingStatusChanged::class);
    }

    public function test_decline_records_declined_at_timestamp(): void
    {
        $booking = $this->createBooking(Booking::STATUS_NEW);

        $this->actingAs($this->worker)
            ->postJson("/worker/jobs/{$booking->id}/decline");

        $booking->refresh();

        $this->assertNotNull($booking->declined_at);
    }

    public function test_decline_records_history(): void
    {
        $booking = $this->createBooking(Booking::STATUS_NEW);

        $this->actingAs($this->worker)
            ->postJson("/worker/jobs/{$booking->id}/decline");

        $this->assertDatabaseHas('booking_histories', [
            'booking_id' => $booking->id,
            'old_status' => Booking::STATUS_NEW,
            'new_status' => Booking::STATUS_DECLINED,
            'user_id'    => $this->worker->id,
        ]);
    }

    public function test_decline_default_reason_when_not_provided(): void
    {
        $booking = $this->createBooking(Booking::STATUS_NEW);

        $this->actingAs($this->worker)
            ->postJson("/worker/jobs/{$booking->id}/decline");

        $this->assertDatabaseHas('bookings', [
            'id'            => $booking->id,
            'decline_reason' => 'Declined by worker',
        ]);
    }

    // --- Authorization ---

    public function test_worker_cannot_decline_others_booking(): void
    {
        $otherWorker = User::factory()->create(['role' => 'worker']);
        $booking = $this->createBooking(Booking::STATUS_NEW);

        $response = $this->actingAs($otherWorker)
            ->postJson("/worker/jobs/{$booking->id}/decline");

        $response->assertStatus(403);
    }

    // --- State validation ---

    public function test_worker_cannot_decline_non_new_booking(): void
    {
        $booking = $this->createBooking(Booking::STATUS_ACCEPTED);

        $response = $this->actingAs($this->worker)
            ->postJson("/worker/jobs/{$booking->id}/decline");

        $response->assertStatus(422)
            ->assertJsonPath('message', "Cannot decline a booking that is 'accepted'. Only 'new' bookings can be declined.");
    }

    public function test_worker_cannot_decline_in_progress_booking(): void
    {
        $booking = $this->createBooking(Booking::STATUS_IN_PROGRESS);

        $response = $this->actingAs($this->worker)
            ->postJson("/worker/jobs/{$booking->id}/decline");

        $response->assertStatus(422);
    }

    public function test_worker_cannot_decline_completed_booking(): void
    {
        $booking = $this->createBooking(Booking::STATUS_COMPLETED);

        $response = $this->actingAs($this->worker)
            ->postJson("/worker/jobs/{$booking->id}/decline");

        $response->assertStatus(422);
    }

    public function test_worker_cannot_decline_already_declined_booking(): void
    {
        $booking = $this->createBooking(Booking::STATUS_DECLINED);

        $response = $this->actingAs($this->worker)
            ->postJson("/worker/jobs/{$booking->id}/decline");

        $response->assertStatus(422);
    }

    public function test_worker_cannot_decline_cancelled_booking(): void
    {
        $booking = $this->createBooking(Booking::STATUS_CANCELLED);

        $response = $this->actingAs($this->worker)
            ->postJson("/worker/jobs/{$booking->id}/decline");

        $response->assertStatus(422);
    }
}
