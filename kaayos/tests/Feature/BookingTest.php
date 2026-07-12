<?php

namespace Tests\Feature;

use App\Events\BookingCreated;
use App\Models\Booking;
use App\Models\User;
use App\Notifications\BookingCancelled;
use App\Notifications\NewBooking;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class BookingTest extends TestCase
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

    protected function validBookingData(array $overrides = []): array
    {
        return array_merge([
            'worker_id'       => $this->worker->id,
            'service_category' => 'Plumbing',
            'scheduled_at'    => now()->addDay()->format('Y-m-d H:i:s'),
            'house_no'        => '123',
            'barangay'        => 'Brgy. Bayanan',
            'notes'           => 'Please bring tools.',
            'price'           => 500.00,
        ], $overrides);
    }

    public function test_guest_cannot_create_booking(): void
    {
        $response = $this->postJson(route('client.bookings.store'), $this->validBookingData());

        $response->assertUnauthorized();
    }

    public function test_client_can_create_booking(): void
    {
        Notification::fake();
        Event::fake();

        $response = $this->actingAs($this->client)
            ->postJson(route('client.bookings.store'), $this->validBookingData());

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['booking' => ['id', 'status']]);

        $this->assertDatabaseHas('bookings', [
            'client_id'        => $this->client->id,
            'worker_id'        => $this->worker->id,
            'status'           => Booking::STATUS_NEW,
            'service_category' => 'Plumbing',
            'price'            => 500.00,
        ]);

        Notification::assertSentTo($this->worker, NewBooking::class);
        Event::assertDispatched(BookingCreated::class);
    }

    public function test_booking_creates_initial_history_entry(): void
    {
        $response = $this->actingAs($this->client)
            ->postJson(route('client.bookings.store'), $this->validBookingData());

        $bookingId = $response->json('booking.id');

        $this->assertDatabaseHas('booking_histories', [
            'booking_id' => $bookingId,
            'old_status' => null,
            'new_status' => Booking::STATUS_NEW,
            'user_id'    => $this->client->id,
        ]);
    }

    public function test_client_cannot_book_suspended_worker(): void
    {
        $this->worker->update(['suspended_at' => now()]);

        $response = $this->actingAs($this->client)
            ->postJson(route('client.bookings.store'), $this->validBookingData());

        $response->assertStatus(422)
            ->assertJsonPath('message', 'This worker is currently unavailable.');
    }

    public function test_client_cannot_book_nonexistent_worker(): void
    {
        $response = $this->actingAs($this->client)
            ->postJson(route('client.bookings.store'), $this->validBookingData([
                'worker_id' => 99999,
            ]));

        $response->assertStatus(422);
    }

    public function test_client_cannot_book_overlapping_slot(): void
    {
        $scheduledAt = now()->addDay()->format('Y-m-d H:i:s');

        Booking::create([
            'client_id'        => User::factory()->create(['role' => 'client'])->id,
            'worker_id'        => $this->worker->id,
            'service_category' => 'Plumbing',
            'scheduled_at'     => $scheduledAt,
            'address'          => 'Address',
            'house_no'         => '123',
            'barangay'         => 'Brgy.',
            'status'           => Booking::STATUS_ACCEPTED,
        ]);

        $response = $this->actingAs($this->client)
            ->postJson(route('client.bookings.store'), $this->validBookingData([
                'scheduled_at' => $scheduledAt,
            ]));

        $response->assertStatus(422)
            ->assertJsonPath('message', 'This worker already has a booking at the selected time.');
    }

    public function test_booking_price_defaults_to_zero(): void
    {
        $response = $this->actingAs($this->client)
            ->postJson(route('client.bookings.store'), $this->validBookingData([
                'price' => null,
            ]));

        $response->assertOk();

        $this->assertDatabaseHas('bookings', [
            'id'    => $response->json('booking.id'),
            'price' => 0.00,
        ]);
    }

    public function test_client_can_cancel_own_booking(): void
    {
        Notification::fake();

        $booking = Booking::create([
            'client_id'        => $this->client->id,
            'worker_id'        => $this->worker->id,
            'service_category' => 'Plumbing',
            'scheduled_at'     => now()->addDay(),
            'address'          => 'Address',
            'house_no'         => '123',
            'barangay'         => 'Brgy.',
            'status'           => Booking::STATUS_NEW,
        ]);

        $response = $this->actingAs($this->client)
            ->postJson(route('client.bookings.cancel', $booking));

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('bookings', [
            'id'     => $booking->id,
            'status' => Booking::STATUS_CANCELLED,
        ]);

        Notification::assertSentTo($this->worker, BookingCancelled::class);
    }

    public function test_client_cannot_cancel_others_booking(): void
    {
        $otherClient = User::factory()->create(['role' => 'client']);

        $booking = Booking::create([
            'client_id'        => $otherClient->id,
            'worker_id'        => $this->worker->id,
            'service_category' => 'Plumbing',
            'scheduled_at'     => now()->addDay(),
            'address'          => 'Address',
            'house_no'         => '123',
            'barangay'         => 'Brgy.',
            'status'           => Booking::STATUS_NEW,
        ]);

        $response = $this->actingAs($this->client)
            ->postJson(route('client.bookings.cancel', $booking));

        $response->assertStatus(403);
    }

    public function test_client_cannot_cancel_completed_booking(): void
    {
        $booking = Booking::create([
            'client_id'        => $this->client->id,
            'worker_id'        => $this->worker->id,
            'service_category' => 'Plumbing',
            'scheduled_at'     => now()->subDay(),
            'address'          => 'Address',
            'house_no'         => '123',
            'barangay'         => 'Brgy.',
            'status'           => Booking::STATUS_COMPLETED,
            'completed_at'     => now(),
        ]);

        $response = $this->actingAs($this->client)
            ->postJson(route('client.bookings.cancel', $booking));

        $response->assertStatus(422);
    }

    public function test_client_can_submit_review_on_completed_booking(): void
    {
        $booking = Booking::create([
            'client_id'        => $this->client->id,
            'worker_id'        => $this->worker->id,
            'service_category' => 'Plumbing',
            'scheduled_at'     => now()->subDay(),
            'address'          => 'Address',
            'house_no'         => '123',
            'barangay'         => 'Brgy.',
            'status'           => Booking::STATUS_COMPLETED,
            'completed_at'     => now(),
        ]);

        $response = $this->actingAs($this->client)
            ->postJson(route('client.bookings.review', $booking), [
                'rating'  => 5,
                'comment' => 'Great work!',
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('reviews', [
            'booking_id' => $booking->id,
            'client_id'  => $this->client->id,
            'worker_id'  => $this->worker->id,
            'rating'     => 5,
        ]);
    }

    public function test_client_cannot_review_non_completed_booking(): void
    {
        $booking = Booking::create([
            'client_id'        => $this->client->id,
            'worker_id'        => $this->worker->id,
            'service_category' => 'Plumbing',
            'scheduled_at'     => now()->addDay(),
            'address'          => 'Address',
            'house_no'         => '123',
            'barangay'         => 'Brgy.',
            'status'           => Booking::STATUS_ACCEPTED,
        ]);

        $response = $this->actingAs($this->client)
            ->postJson(route('client.bookings.review', $booking), [
                'rating'  => 5,
                'comment' => 'Great work!',
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Can only review completed bookings.');
    }

    public function test_cancel_records_history(): void
    {
        $booking = Booking::create([
            'client_id'        => $this->client->id,
            'worker_id'        => $this->worker->id,
            'service_category' => 'Plumbing',
            'scheduled_at'     => now()->addDay(),
            'address'          => 'Address',
            'house_no'         => '123',
            'barangay'         => 'Brgy.',
            'status'           => Booking::STATUS_NEW,
        ]);

        $this->actingAs($this->client)
            ->postJson(route('client.bookings.cancel', $booking));

        $this->assertDatabaseHas('booking_histories', [
            'booking_id' => $booking->id,
            'old_status' => Booking::STATUS_NEW,
            'new_status' => Booking::STATUS_CANCELLED,
        ]);
    }
}
