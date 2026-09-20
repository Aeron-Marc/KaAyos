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

class LiveTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected User $client;
    protected User $worker;
    protected User $otherClient;
    protected User $otherWorker;
    protected Booking $booking;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = User::factory()->create([
            'role'       => 'client',
            'first_name' => 'Juan',
            'last_name'  => 'Client',
            'barangay'   => 'Luna',
            'latitude'   => 14.0192,
            'longitude'  => 120.7353,
        ]);

        $this->otherClient = User::factory()->create([
            'role'       => 'client',
            'first_name' => 'Other',
            'last_name'  => 'Client',
        ]);

        $this->worker = User::factory()->create([
            'role'       => 'worker',
            'first_name' => 'Pedro',
            'last_name'  => 'Panday',
            'barangay'   => 'Rizal',
            'latitude'   => 14.0175,
            'longitude'  => 120.7290,
        ]);

        $this->otherWorker = User::factory()->create([
            'role'       => 'worker',
            'first_name' => 'Maria',
            'last_name'  => 'Carpenter',
        ]);

        WorkerProfile::create([
            'user_id'           => $this->worker->id,
            'service_radius_km' => 20,
            'current_latitude'  => 14.0175,
            'current_longitude' => 120.7290,
        ]);

        $this->booking = Booking::create([
            'client_id'         => $this->client->id,
            'worker_id'         => $this->worker->id,
            'service_category'  => 'Carpentry',
            'scheduled_at'      => Carbon::tomorrow()->setHour(10)->setMinute(0),
            'address'           => '123 Main St',
            'barangay'          => 'Luna',
            'latitude'          => 14.0192,
            'longitude'         => 120.7353,
            'price'             => 600,
            'status'            => Booking::STATUS_EN_ROUTE,
            'status_history'    => [
                'new'      => Carbon::yesterday()->toIso8601String(),
                'accepted' => Carbon::yesterday()->addHours(2)->toIso8601String(),
                'en_route' => Carbon::now()->toIso8601String(),
            ],
        ]);
    }

    public function test_worker_can_send_live_location_ping_when_en_route(): void
    {
        $pingLat = 14.0210;
        $pingLng = 120.7315;

        $response = $this->actingAs($this->worker)
            ->postJson(route('worker.jobs.location-ping', $this->booking), [
                'latitude'  => $pingLat,
                'longitude' => $pingLng,
                'heading'   => 45.5,
                'speed'     => 8.2,
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'remaining_km',
                'formatted_dist',
                'remaining_minutes',
                'formatted_time',
                'eta_time',
                'live_tracked_at',
            ]);

        $this->booking->refresh();
        $this->assertEquals($pingLat, (float) $this->booking->worker_live_latitude);
        $this->assertEquals($pingLng, (float) $this->booking->worker_live_longitude);
        $this->assertEquals(45.5, (float) $this->booking->worker_live_heading);
        $this->assertNotNull($this->booking->worker_live_updated_at);
        $this->assertNotNull($this->booking->travel_distance_from_prev_km);
    }

    public function test_worker_cannot_send_location_ping_when_status_is_not_en_route(): void
    {
        $this->booking->update(['status' => Booking::STATUS_ACCEPTED]);

        $response = $this->actingAs($this->worker)
            ->postJson(route('worker.jobs.location-ping', $this->booking), [
                'latitude'  => 14.0200,
                'longitude' => 120.7300,
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'status'  => Booking::STATUS_ACCEPTED,
            ]);
    }

    public function test_unauthorized_worker_cannot_send_location_ping(): void
    {
        $response = $this->actingAs($this->otherWorker)
            ->postJson(route('worker.jobs.location-ping', $this->booking), [
                'latitude'  => 14.0200,
                'longitude' => 120.7300,
            ]);

        $response->assertStatus(403);
    }

    public function test_client_can_track_worker_live_location(): void
    {
        // First simulate worker ping
        $pingLat = 14.0220;
        $pingLng = 120.7320;

        $this->booking->update([
            'worker_live_latitude'   => $pingLat,
            'worker_live_longitude'  => $pingLng,
            'worker_live_heading'    => 90.0,
            'worker_live_speed'      => 6.5,
            'worker_live_updated_at' => now(),
        ]);

        $response = $this->actingAs($this->client)
            ->getJson(route('client.bookings.track', $this->booking));

        $response->assertOk()
            ->assertJson([
                'success'     => true,
                'status'      => Booking::STATUS_EN_ROUTE,
                'arrived'     => false,
                'worker_name' => 'Pedro Panday',
                'service'     => 'Carpentry',
                'worker_lat'  => $pingLat,
                'worker_lng'  => $pingLng,
                'is_live'     => true,
            ])
            ->assertJsonStructure([
                'remaining_km',
                'formatted_dist',
                'remaining_minutes',
                'formatted_time',
                'eta_time',
                'dest_lat',
                'dest_lng',
                'last_ping_seconds',
            ]);
    }

    public function test_client_sees_arrived_when_job_starts_in_progress(): void
    {
        $this->booking->update(['status' => Booking::STATUS_IN_PROGRESS]);

        $response = $this->actingAs($this->client)
            ->getJson(route('client.bookings.track', $this->booking));

        $response->assertOk()
            ->assertJson([
                'success'     => true,
                'status'      => Booking::STATUS_IN_PROGRESS,
                'arrived'     => true,
                'worker_name' => 'Pedro Panday',
            ]);
    }

    public function test_other_client_cannot_track_foreign_booking(): void
    {
        $response = $this->actingAs($this->otherClient)
            ->getJson(route('client.bookings.track', $this->booking));

        $response->assertStatus(403);
    }
}
