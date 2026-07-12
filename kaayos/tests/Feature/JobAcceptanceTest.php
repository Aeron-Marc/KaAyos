<?php

namespace Tests\Feature;

use App\Exceptions\BookingStateException;
use App\Models\Booking;
use App\Models\Earning;
use App\Models\User;
use App\Notifications\BookingStatusChanged;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class JobAcceptanceTest extends TestCase
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

    public function test_guest_cannot_update_job_status(): void
    {
        $booking = $this->createBooking();

        $response = $this->patchJson("/worker/jobs/{$booking->id}/status", [
            'status' => Booking::STATUS_ACCEPTED,
        ]);

        $response->assertUnauthorized();
    }

    public function test_worker_can_accept_new_booking(): void
    {
        Notification::fake();

        $booking = $this->createBooking(Booking::STATUS_NEW);

        $response = $this->actingAs($this->worker)
            ->patchJson("/worker/jobs/{$booking->id}/status", [
                'status' => Booking::STATUS_ACCEPTED,
            ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Job status updated successfully.');

        $this->assertDatabaseHas('bookings', [
            'id'     => $booking->id,
            'status' => Booking::STATUS_ACCEPTED,
        ]);

        Notification::assertSentTo($this->client, BookingStatusChanged::class);
    }

    public function test_worker_cannot_accept_others_booking(): void
    {
        $otherWorker = User::factory()->create(['role' => 'worker']);
        $booking = $this->createBooking(Booking::STATUS_NEW);

        $response = $this->actingAs($otherWorker)
            ->patchJson("/worker/jobs/{$booking->id}/status", [
                'status' => Booking::STATUS_ACCEPTED,
            ]);

        $response->assertStatus(403);
    }

    public function test_worker_cannot_skip_statuses(): void
    {
        $booking = $this->createBooking(Booking::STATUS_NEW);

        $response = $this->actingAs($this->worker)
            ->patchJson("/worker/jobs/{$booking->id}/status", [
                'status' => Booking::STATUS_EN_ROUTE,
            ]);

        $response->assertStatus(422);
    }

    public function test_worker_cannot_go_backward(): void
    {
        $booking = $this->createBooking(Booking::STATUS_ACCEPTED);

        $response = $this->actingAs($this->worker)
            ->patchJson("/worker/jobs/{$booking->id}/status", [
                'status' => Booking::STATUS_NEW,
            ]);

        $response->assertStatus(422);
    }

    public function test_worker_can_transition_en_route_to_in_progress(): void
    {
        $booking = $this->createBooking(Booking::STATUS_EN_ROUTE);

        $response = $this->actingAs($this->worker)
            ->patchJson("/worker/jobs/{$booking->id}/status", [
                'status' => Booking::STATUS_IN_PROGRESS,
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('bookings', [
            'id'     => $booking->id,
            'status' => Booking::STATUS_IN_PROGRESS,
        ]);
    }

    public function test_worker_can_transition_in_progress_to_completed_and_earning_created(): void
    {
        Notification::fake();

        $booking = $this->createBooking(Booking::STATUS_IN_PROGRESS);

        $response = $this->actingAs($this->worker)
            ->patchJson("/worker/jobs/{$booking->id}/status", [
                'status' => Booking::STATUS_COMPLETED,
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('bookings', [
            'id'     => $booking->id,
            'status' => Booking::STATUS_COMPLETED,
        ]);

        $this->assertDatabaseHas('earnings', [
            'booking_id'   => $booking->id,
            'worker_id'    => $this->worker->id,
            'gross_amount' => 500.00,
            'platform_fee' => 50.00,
            'net_amount'   => 450.00,
        ]);

        $this->assertNotNull($booking->fresh()->completed_at);
    }

    public function test_worker_reaches_max_concurrent_jobs(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $otherClient = User::factory()->create(['role' => 'client']);
            Booking::create([
                'client_id'        => $otherClient->id,
                'worker_id'        => $this->worker->id,
                'service_category' => 'Plumbing',
                'scheduled_at'     => now()->addDays($i + 1),
                'address'          => 'Address',
                'house_no'         => '123',
                'barangay'         => 'Brgy.',
                'status'           => Booking::STATUS_ACCEPTED,
            ]);
        }

        $booking = $this->createBooking(Booking::STATUS_NEW);

        $response = $this->actingAs($this->worker)
            ->patchJson("/worker/jobs/{$booking->id}/status", [
                'status' => Booking::STATUS_ACCEPTED,
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'You have reached the maximum of 3 concurrent jobs. Complete an existing job first.');
    }

    public function test_worker_can_cancel_active_booking(): void
    {
        $booking = $this->createBooking(Booking::STATUS_ACCEPTED);

        $response = $this->actingAs($this->worker)
            ->patchJson("/worker/jobs/{$booking->id}/status", [
                'status' => Booking::STATUS_CANCELLED,
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('bookings', [
            'id'     => $booking->id,
            'status' => Booking::STATUS_CANCELLED,
        ]);
    }

    public function test_booking_history_recorded_on_each_transition(): void
    {
        $booking = $this->createBooking(Booking::STATUS_NEW);

        $this->actingAs($this->worker)
            ->patchJson("/worker/jobs/{$booking->id}/status", [
                'status' => Booking::STATUS_ACCEPTED,
            ]);

        $this->assertDatabaseHas('booking_histories', [
            'booking_id' => $booking->id,
            'old_status' => Booking::STATUS_NEW,
            'new_status' => Booking::STATUS_ACCEPTED,
            'user_id'    => $this->worker->id,
        ]);
    }

    public function test_optimistic_lock_prevents_race_condition(): void
    {
        $booking = $this->createBooking(Booking::STATUS_NEW);

        $this->actingAs($this->worker)
            ->patchJson("/worker/jobs/{$booking->id}/status", [
                'status' => Booking::STATUS_ACCEPTED,
            ]);

        $this->expectException(BookingStateException::class);

        $booking->transitionTo(Booking::STATUS_ACCEPTED);
    }

    public function test_cancelled_status_not_in_allowed_transitions_when_inactive(): void
    {
        $completed = $this->createBooking(Booking::STATUS_COMPLETED);

        $response = $this->actingAs($this->worker)
            ->patchJson("/worker/jobs/{$completed->id}/status", [
                'status' => Booking::STATUS_CANCELLED,
            ]);

        $response->assertStatus(422);
    }
}
