<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use App\Models\WorkerProfile;
use App\Services\GeoTravelService;
use App\Support\TuyBarangays;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GeoTravelSchedulingTest extends TestCase
{
    use RefreshDatabase;

    protected User $client;
    protected User $worker;
    protected GeoTravelService $geoTravel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->geoTravel = app(GeoTravelService::class);

        $this->client = User::factory()->create([
            'role'      => 'client',
            'barangay'  => 'Luna',
            'latitude'  => 14.0192,
            'longitude' => 120.7353,
        ]);

        $this->worker = User::factory()->create([
            'role'      => 'worker',
            'barangay'  => 'Luna',
            'latitude'  => 14.0192,
            'longitude' => 120.7353,
        ]);

        WorkerProfile::create([
            'user_id'           => $this->worker->id,
            'service_radius_km' => 25,
            'availability'      => [
                ['day' => 'Monday', 'active' => true, 'start' => '07:00', 'end' => '19:00'],
                ['day' => 'Tuesday', 'active' => true, 'start' => '07:00', 'end' => '19:00'],
                ['day' => 'Wednesday', 'active' => true, 'start' => '07:00', 'end' => '19:00'],
                ['day' => 'Thursday', 'active' => true, 'start' => '07:00', 'end' => '19:00'],
                ['day' => 'Friday', 'active' => true, 'start' => '07:00', 'end' => '19:00'],
                ['day' => 'Saturday', 'active' => true, 'start' => '07:00', 'end' => '19:00'],
                ['day' => 'Sunday', 'active' => true, 'start' => '07:00', 'end' => '19:00'],
            ],
        ]);
    }

    public function test_geotravel_service_calculates_distance_and_transit_time(): void
    {
        // Luna to Sabang (across Tuy)
        [$lat1, $lng1] = TuyBarangays::pointForStatic('Luna');
        [$lat2, $lng2] = TuyBarangays::pointForStatic('Sabang');

        $result = $this->geoTravel->calculateTravel($lat1, $lng1, $lat2, $lng2);

        $this->assertArrayHasKey('straight_distance_km', $result);
        $this->assertArrayHasKey('road_distance_km', $result);
        $this->assertArrayHasKey('estimated_minutes', $result);
        $this->assertArrayHasKey('formatted_distance', $result);
        $this->assertArrayHasKey('formatted_time', $result);

        // Road distance should account for tortuosity factor (1.25x)
        $this->assertGreaterThan($result['straight_distance_km'], $result['road_distance_km']);
        $this->assertGreaterThan(0, $result['estimated_minutes']);
    }

    public function test_geotravel_evaluates_schedule_feasibility(): void
    {
        // Job 1 at 9:00 AM in Sabang (duration 2 hrs -> finishes at 11:00 AM)
        $scheduledAt = Carbon::tomorrow()->setTime(9, 0);
        [$sabangLat, $sabangLng] = TuyBarangays::pointForStatic('Sabang');

        $job1 = Booking::create([
            'client_id'                    => $this->client->id,
            'worker_id'                    => $this->worker->id,
            'service_category'             => 'Carpentry',
            'scheduled_at'                 => $scheduledAt,
            'address'                      => '123 Sabang, Tuy, Batangas',
            'barangay'                     => 'Sabang',
            'latitude'                     => $sabangLat,
            'longitude'                    => $sabangLng,
            'status'                       => Booking::STATUS_ACCEPTED,
        ]);

        [$lunaLat, $lunaLng] = TuyBarangays::pointForStatic('Luna');

        // New job requested at 11:05 AM in Luna (only 5 mins buffer, but transit Sabang->Luna takes ~15-20 mins)
        $tightStart = Carbon::tomorrow()->setTime(11, 5);
        $conflictRes = $this->geoTravel->evaluateScheduleFeasibility($job1, $tightStart, $lunaLat, $lunaLng, 120);

        $this->assertEquals('conflict', $conflictRes['status']);
        $this->assertNotEmpty($conflictRes['recommended_time']);

        // New job requested at 1:00 PM in Luna (2 hours buffer -> plenty of time)
        $feasibleStart = Carbon::tomorrow()->setTime(13, 0);
        $feasibleRes = $this->geoTravel->evaluateScheduleFeasibility($job1, $feasibleStart, $lunaLat, $lunaLng, 120);

        $this->assertEquals('feasible', $feasibleRes['status']);
    }

    public function test_booking_creation_rejects_schedule_with_insufficient_travel_buffer(): void
    {
        $existingTime = Carbon::tomorrow()->setTime(9, 0);
        [$sabangLat, $sabangLng] = TuyBarangays::pointForStatic('Sabang');

        Booking::create([
            'client_id'                    => $this->client->id,
            'worker_id'                    => $this->worker->id,
            'service_category'             => 'Carpentry',
            'scheduled_at'                 => $existingTime,
            'address'                      => '100 Sabang, Tuy',
            'barangay'                     => 'Sabang',
            'latitude'                     => $sabangLat,
            'longitude'                    => $sabangLng,
            'status'                       => Booking::STATUS_ACCEPTED,
        ]);

        // Client attempts to book at 11:05 AM in Guinhawa (insufficient travel buffer after 2-hour job)
        $response = $this->actingAs($this->client)->postJson(route('client.bookings.store'), [
            'worker_id'        => $this->worker->id,
            'service_category' => 'Plumbing',
            'scheduled_at'     => Carbon::tomorrow()->setTime(11, 5)->format('Y-m-d H:i:s'),
            'house_no'         => '456',
            'barangay'         => 'Guinhawa',
            'notes'            => 'Pipe leak',
            'price'            => 600,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success'  => false,
                'conflict' => true,
            ]);

        $this->assertArrayHasKey('recommended_time', $response->json());
    }

    public function test_booking_creation_succeeds_when_feasible_and_stores_geo_metrics(): void
    {
        $feasibleTime = Carbon::tomorrow()->setTime(14, 0);

        $response = $this->actingAs($this->client)->postJson(route('client.bookings.store'), [
            'worker_id'        => $this->worker->id,
            'service_category' => 'Plumbing',
            'scheduled_at'     => $feasibleTime->format('Y-m-d H:i:s'),
            'house_no'         => '456',
            'barangay'         => 'Luna',
            'notes'            => 'Pipe leak repair',
            'price'            => 600,
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $booking = Booking::latest()->first();
        $this->assertNotNull($booking);
        $this->assertEquals('Luna', $booking->barangay);
        $this->assertNotNull($booking->latitude);
        $this->assertNotNull($booking->longitude);
        $this->assertNotNull($booking->estimated_transit_minutes);
        $this->assertNotNull($booking->google_maps_nav_url);
    }

    public function test_booking_creation_enforces_worker_service_radius(): void
    {
        // Set worker radius to 2 km
        $this->worker->workerProfile->update(['service_radius_km' => 2]);

        // Worker is in Luna. Sabang is ~6-8 km away road travel
        $farTime = Carbon::tomorrow()->setTime(14, 0);

        $response = $this->actingAs($this->client)->postJson(route('client.bookings.store'), [
            'worker_id'        => $this->worker->id,
            'service_category' => 'Plumbing',
            'scheduled_at'     => $farTime->format('Y-m-d H:i:s'),
            'house_no'         => '999',
            'barangay'         => 'Sabang',
            'notes'            => 'Remote farm work',
            'price'            => 700,
        ]);

        $response->assertStatus(422)
            ->assertJsonFragment(['success' => false]);

        $this->assertStringContainsString('service radius', $response->json('message'));
    }

    public function test_check_schedule_conflict_api_returns_status(): void
    {
        $targetDate = Carbon::tomorrow()->setTime(10, 0);

        $response = $this->actingAs($this->client)->getJson(
            route('client.workers.check-schedule', [
                'worker'       => $this->worker->id,
                'scheduled_at' => $targetDate->format('Y-m-d H:i:s'),
                'barangay'     => 'Luna',
            ])
        );

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status'  => 'feasible',
            ]);
    }

    public function test_worker_daily_route_returns_ordered_itinerary_and_waypoints(): void
    {
        $date = Carbon::tomorrow();

        // Create 2 sequential jobs
        Booking::create([
            'client_id'                    => $this->client->id,
            'worker_id'                    => $this->worker->id,
            'service_category'             => 'Carpentry',
            'scheduled_at'                 => $date->copy()->setTime(8, 0),
            'address'                      => 'Stop 1, Luna',
            'barangay'                     => 'Luna',
            'latitude'                     => 14.0192,
            'longitude'                    => 120.7353,
            'status'                       => Booking::STATUS_ACCEPTED,
        ]);

        Booking::create([
            'client_id'                    => $this->client->id,
            'worker_id'                    => $this->worker->id,
            'service_category'             => 'Electrical',
            'scheduled_at'                 => $date->copy()->setTime(13, 0),
            'address'                      => 'Stop 2, Sabang',
            'barangay'                     => 'Sabang',
            'latitude'                     => 14.0576,
            'longitude'                    => 120.7080,
            'status'                       => Booking::STATUS_ACCEPTED,
        ]);

        $response = $this->actingAs($this->worker)->getJson(
            route('worker.calendar.route', ['date' => $date->toDateString()])
        );

        $response->assertStatus(200)
            ->assertJsonStructure([
                'date',
                'formatted_date',
                'itinerary' => [
                    'total_jobs',
                    'total_distance_km',
                    'total_transit_minutes',
                    'waypoints',
                    'legs',
                ],
            ]);

        $this->assertEquals(2, $response->json('itinerary.total_jobs'));
        $this->assertNotEmpty($response->json('itinerary.waypoints'));
        $this->assertNotEmpty($response->json('itinerary.legs'));
    }
}
