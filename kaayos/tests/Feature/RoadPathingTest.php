<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use App\Models\WorkerProfile;
use App\Services\GeoTravelService;
use App\Support\TuyBarangays;
use App\Support\TuyRoadNodes;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RoadPathingTest extends TestCase
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
            'role'       => 'client',
            'first_name' => 'Juan',
            'last_name'  => 'Dela Cruz',
            'barangay'   => 'Luna',
            'latitude'   => 14.0192,
            'longitude'  => 120.7353,
        ]);

        $this->worker = User::factory()->create([
            'role'       => 'worker',
            'first_name' => 'Pedro',
            'last_name'  => 'Panday',
            'barangay'   => 'Rizal',
            'latitude'   => 14.0175,
            'longitude'  => 120.7290,
        ]);

        WorkerProfile::create([
            'user_id'           => $this->worker->id,
            'service_radius_km' => 25,
            'current_latitude'  => 14.0175,
            'current_longitude' => 120.7290,
        ]);
    }

    public function test_geotravel_fetches_road_geometry_with_osrm_mock(): void
    {
        Http::fake([
            'router.project-osrm.org/*' => Http::response([
                'code'   => 'Ok',
                'routes' => [
                    [
                        'distance' => 3450.0, // 3.45 km
                        'duration' => 420.0,  // 7 mins
                        'geometry' => [
                            'coordinates' => [
                                [120.7290, 14.0175],
                                [120.7310, 14.0180],
                                [120.7353, 14.0192],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $res = $this->geoTravel->fetchRoadRoute(14.0175, 120.7290, 14.0192, 120.7353);

        $this->assertTrue($res['is_osrm']);
        $this->assertEquals(3.45, $res['road_distance_km']);
        $this->assertCount(3, $res['geometry']);
        // Verify coordinate inversion: GeoJSON [lng, lat] -> Leaflet [lat, lng]
        $this->assertEquals([14.0175, 120.7290], $res['geometry'][0]);
        $this->assertEquals([14.0192, 120.7353], $res['geometry'][2]);
    }

    public function test_geotravel_falls_back_to_tuy_road_nodes_when_osrm_unreachable(): void
    {
        Http::fake([
            'router.project-osrm.org/*' => Http::response(null, 500),
        ]);

        $res = $this->geoTravel->fetchRoadRoute(14.0175, 120.7290, 14.0080, 120.7550);

        $this->assertFalse($res['is_osrm']);
        $this->assertGreaterThan(0, $res['road_distance_km']);
        $this->assertNotEmpty($res['geometry']);
        // Verify start and end points match
        $this->assertEquals([14.0175, 120.7290], $res['geometry'][0]);
        $this->assertEquals([14.0080, 120.7550], end($res['geometry']));
    }

    public function test_client_live_tracking_includes_road_route_coordinates(): void
    {
        Http::fake([
            'router.project-osrm.org/*' => Http::response([
                'code'   => 'Ok',
                'routes' => [
                    [
                        'distance' => 1200.0,
                        'duration' => 180.0,
                        'geometry' => [
                            'coordinates' => [
                                [120.7300, 14.0200],
                                [120.7320, 14.0210],
                                [120.7353, 14.0192],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $booking = Booking::create([
            'client_id'              => $this->client->id,
            'worker_id'              => $this->worker->id,
            'service_category'       => 'Carpentry',
            'scheduled_at'           => Carbon::tomorrow()->setHour(9),
            'address'                => 'Main St',
            'barangay'               => 'Luna',
            'latitude'               => 14.0192,
            'longitude'              => 120.7353,
            'status'                 => Booking::STATUS_EN_ROUTE,
            'worker_live_latitude'   => 14.0200,
            'worker_live_longitude'  => 120.7300,
            'worker_live_updated_at' => now(),
        ]);

        $response = $this->actingAs($this->client)
            ->getJson(route('client.bookings.track', $booking));

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'status'  => 'en_route',
            ])
            ->assertJsonStructure([
                'route_coordinates',
                'is_snapped_road',
                'remaining_km',
                'formatted_dist',
            ]);

        $data = $response->json();
        $this->assertIsArray($data['route_coordinates']);
        $this->assertNotEmpty($data['route_coordinates']);
    }

    public function test_worker_calendar_route_includes_road_geometry(): void
    {
        Http::fake([
            'router.project-osrm.org/*' => Http::response([
                'code'   => 'Ok',
                'routes' => [
                    [
                        'distance' => 2000.0,
                        'duration' => 300.0,
                        'geometry' => [
                            'coordinates' => [
                                [120.7290, 14.0175],
                                [120.7353, 14.0192],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $targetDate = Carbon::tomorrow();

        Booking::create([
            'client_id'        => $this->client->id,
            'worker_id'        => $this->worker->id,
            'service_category' => 'Carpentry',
            'scheduled_at'     => $targetDate->copy()->setHour(10),
            'address'          => 'Main St',
            'barangay'         => 'Luna',
            'latitude'         => 14.0192,
            'longitude'        => 120.7353,
            'status'           => Booking::STATUS_ACCEPTED,
        ]);

        $response = $this->actingAs($this->worker)
            ->getJson(route('worker.calendar.route', ['date' => $targetDate->toDateString()]));

        $response->assertOk()
            ->assertJsonStructure([
                'date',
                'formatted_date',
                'itinerary' => [
                    'total_jobs',
                    'total_distance_km',
                    'legs' => [
                        '*' => [
                            'from_title',
                            'to_title',
                            'distance_km',
                            'geometry',
                        ],
                    ],
                ],
            ]);
    }

    public function test_worker_can_fetch_route_optimization(): void
    {
        $targetDate = Carbon::tomorrow();

        // Create 3 bookings across different barangays in Tuy
        Booking::create([
            'client_id'        => $this->client->id,
            'worker_id'        => $this->worker->id,
            'service_category' => 'Plumbing',
            'scheduled_at'     => $targetDate->copy()->setHour(9),
            'address'          => 'Sabang St',
            'barangay'         => 'Sabang',
            'latitude'         => 14.0080,
            'longitude'        => 120.7550,
            'status'           => Booking::STATUS_ACCEPTED,
        ]);

        Booking::create([
            'client_id'        => $this->client->id,
            'worker_id'        => $this->worker->id,
            'service_category' => 'Carpentry',
            'scheduled_at'     => $targetDate->copy()->setHour(13),
            'address'          => 'Luna St',
            'barangay'         => 'Luna',
            'latitude'         => 14.0192,
            'longitude'        => 120.7353,
            'status'           => Booking::STATUS_ACCEPTED,
        ]);

        Booking::create([
            'client_id'        => $this->client->id,
            'worker_id'        => $this->worker->id,
            'service_category' => 'Electrical',
            'scheduled_at'     => $targetDate->copy()->setHour(16),
            'address'          => 'Putol St',
            'barangay'         => 'Putol',
            'latitude'         => 14.0150,
            'longitude'        => 120.7420,
            'status'           => Booking::STATUS_ACCEPTED,
        ]);

        $response = $this->actingAs($this->worker)
            ->getJson(route('worker.calendar.optimize-route', ['date' => $targetDate->toDateString()]));

        $response->assertOk()
            ->assertJsonStructure([
                'date',
                'formatted_date',
                'optimization' => [
                    'has_optimization',
                    'original_distance_km',
                    'optimized_distance_km',
                    'saved_distance_km',
                    'saved_minutes',
                    'formatted_savings',
                    'ordered_bookings' => [
                        '*' => [
                            'id',
                            'order',
                            'service',
                            'client',
                            'barangay',
                        ],
                    ],
                ],
            ]);

        $opt = $response->json('optimization');
        $this->assertCount(3, $opt['ordered_bookings']);
    }
}

