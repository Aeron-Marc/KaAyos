<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\IssueCategory;
use App\Models\User;
use App\Models\WorkerProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EstimatedPricingTest extends TestCase
{
    use RefreshDatabase;

    protected User $client;
    protected User $worker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = User::factory()->create(['role' => 'client']);
        $this->worker = User::factory()->create(['role' => 'worker']);

        WorkerProfile::create([
            'user_id'      => $this->worker->id,
            'hourly_rate'  => 350,
            'availability' => [
                ['day' => 'Monday', 'active' => true, 'start' => '08:00', 'end' => '17:00'],
                ['day' => 'Tuesday', 'active' => true, 'start' => '08:00', 'end' => '17:00'],
                ['day' => 'Wednesday', 'active' => true, 'start' => '08:00', 'end' => '17:00'],
                ['day' => 'Thursday', 'active' => true, 'start' => '08:00', 'end' => '17:00'],
                ['day' => 'Friday', 'active' => true, 'start' => '08:00', 'end' => '17:00'],
                ['day' => 'Saturday', 'active' => false, 'start' => null, 'end' => null],
                ['day' => 'Sunday', 'active' => false, 'start' => null, 'end' => null],
            ],
        ]);
    }

    protected function makeBooking(array $overrides = []): Booking
    {
        return Booking::create(array_merge([
            'client_id'         => $this->client->id,
            'worker_id'         => $this->worker->id,
            'service_category'  => 'Plumbing',
            'issue_category_id' => IssueCategory::first()->id,
            'urgency'           => 'normal',
            'scheduled_at'      => now()->addDays(2)->setTime(10, 0),
            'address'           => '123, Brgy. Luna, Tuy, Batangas',
            'house_no'          => '123',
            'barangay'          => 'Luna',
            'status'            => Booking::STATUS_NEW,
            'price'             => 500.00,
        ], $overrides));
    }

    public function test_new_booking_price_is_estimated(): void
    {
        $booking = $this->makeBooking(['status' => Booking::STATUS_NEW]);

        $this->assertFalse($booking->isScopeConfirmed());
        $this->assertTrue($booking->isPriceEstimated());
    }

    public function test_accepted_booking_price_is_confirmed(): void
    {
        $booking = $this->makeBooking(['status' => Booking::STATUS_ACCEPTED]);

        $this->assertTrue($booking->isScopeConfirmed());
        $this->assertFalse($booking->isPriceEstimated());
    }

    public function test_in_progress_and_completed_bookings_are_confirmed(): void
    {
        $inProgress = $this->makeBooking(['status' => Booking::STATUS_IN_PROGRESS]);
        $completed  = $this->makeBooking(['status' => Booking::STATUS_COMPLETED]);

        $this->assertFalse($inProgress->isPriceEstimated());
        $this->assertFalse($completed->isPriceEstimated());
    }

    public function test_cancelled_and_declined_bookings_stay_estimated(): void
    {
        $cancelled = $this->makeBooking(['status' => Booking::STATUS_CANCELLED]);
        $declined  = $this->makeBooking(['status' => Booking::STATUS_DECLINED]);

        $this->assertTrue($cancelled->isPriceEstimated());
        $this->assertTrue($declined->isPriceEstimated());
    }

    public function test_pending_scope_amendment_marks_price_estimated(): void
    {
        $booking = $this->makeBooking([
            'status'                 => Booking::STATUS_ACCEPTED,
            'scope_amendment_status' => 'pending',
        ]);

        $this->assertTrue($booking->isPriceEstimated());

        $booking->scope_amendment_status = 'accepted';
        $this->assertFalse($booking->isPriceEstimated());
    }

    public function test_worker_dashboard_shows_total_estimated_earnings(): void
    {
        $this->makeBooking(['status' => Booking::STATUS_NEW]);

        $this->actingAs($this->worker)
            ->get('/worker/dashboard')
            ->assertOk()
            ->assertSee('Total Estimated Earnings')
            ->assertSee('pipeline');
    }

    public function test_client_bookings_page_flags_estimated_prices(): void
    {
        $this->makeBooking(['status' => Booking::STATUS_NEW]);

        $this->actingAs($this->client)
            ->get('/client/bookings')
            ->assertOk()
            ->assertSee('is_price_estimated')
            ->assertSee('Est.');
    }

    public function test_calendar_payload_exposes_estimated_flag(): void
    {
        $booking = $this->makeBooking([
            'status'       => Booking::STATUS_NEW,
            'scheduled_at' => now()->addDays(2)->setTime(10, 0),
        ]);

        $response = $this->actingAs($this->worker)->getJson(
            '/worker/calendar/data?year=' . now()->year . '&month=' . now()->month
        );

        $response->assertOk();

        $events = collect($response->json('days'))->flatten(1);
        $event  = $events->firstWhere('id', $booking->id);

        $this->assertNotNull($event, 'Booking missing from calendar payload.');
        $this->assertTrue($event['is_price_estimated']);
        $this->assertSame(500.0, (float) $event['price']);
    }

    public function test_completed_calendar_event_is_not_estimated(): void
    {
        $booking = $this->makeBooking([
            'status'       => Booking::STATUS_COMPLETED,
            'scheduled_at' => now()->addDays(2)->setTime(10, 0),
        ]);

        $events = collect(
            $this->actingAs($this->worker)
                ->getJson('/worker/calendar/data?year=' . now()->year . '&month=' . now()->month)
                ->assertOk()
                ->json('days')
        )->flatten(1);

        $event = $events->firstWhere('id', $booking->id);

        $this->assertNotNull($event);
        $this->assertFalse($event['is_price_estimated']);
    }

    public function test_search_filters_active_availability_workers(): void
    {
        $inactive = User::factory()->create(['role' => 'worker']);
        WorkerProfile::create([
            'user_id'      => $inactive->id,
            'hourly_rate'  => 300,
            'availability' => [
                ['day' => 'Monday', 'active' => false, 'start' => null, 'end' => null],
            ],
        ]);

        $response = $this->get('/search');

        $response->assertOk();
        $response->assertSee($this->worker->name);
        $response->assertDontSee($inactive->name);
    }

    public function test_worker_profile_cards_show_estimate_rate(): void
    {
        $this->actingAs($this->client)
            ->get('/client/workers')
            ->assertOk()
            ->assertSee('Est.');
    }
}
