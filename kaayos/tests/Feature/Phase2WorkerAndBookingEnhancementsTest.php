<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use App\Models\WorkerProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase2WorkerAndBookingEnhancementsTest extends TestCase
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
            'user_id' => $this->worker->id,
            'hourly_rate' => 300,
            'availability' => [
                ['day' => 'Monday', 'active' => true, 'start' => '08:00', 'end' => '17:00'],
                ['day' => 'Tuesday', 'active' => true, 'start' => '08:00', 'end' => '17:00'],
                ['day' => 'Wednesday', 'active' => true, 'start' => '08:00', 'end' => '17:00'],
                ['day' => 'Thursday', 'active' => true, 'start' => '08:00', 'end' => '17:00'],
                ['day' => 'Friday', 'active' => true, 'start' => '08:00', 'end' => '17:00'],
                ['day' => 'Saturday', 'active' => true, 'start' => '08:00', 'end' => '17:00'],
                ['day' => 'Sunday', 'active' => true, 'start' => '08:00', 'end' => '17:00'],
            ],
        ]);
    }

    public function test_client_booking_stores_time_and_work_parameters_with_complexity_multiplier(): void
    {
        $scheduledTime = now()->addDays(2)->setHour(10)->setMinute(0)->setSecond(0);

        $response = $this->actingAs($this->client)->postJson(route('client.bookings.store'), [
            'worker_id'               => $this->worker->id,
            'service_category'        => 'Electrical',
            'scheduled_at'            => $scheduledTime->format('Y-m-d H:i:s'),
            'house_no'                => 'Unit 4B',
            'barangay'                => 'Brgy. Poblacion',
            'property_type'           => 'commercial',
            'pricing_type'            => 'hourly',
            'estimated_duration_hours'=> 3,
            'complexity_level'        => 'complex',
            'notes'                   => 'Need commercial 3-phase rewire',
            'price'                   => 1000,
        ]);

        $response->assertOk()
            ->assertJson(['success' => true]);
        
        $booking = Booking::where('worker_id', $this->worker->id)->latest()->first();
        $this->assertNotNull($booking);
        $this->assertEquals('commercial', $booking->property_type);
        $this->assertEquals('hourly', $booking->pricing_type);
        $this->assertEquals(3.0, (float) $booking->estimated_duration_hours);
        $this->assertEquals('complex', $booking->complexity_level);
        $this->assertEquals(1.2, (float) $booking->complexity_multiplier);

        // Hourly base 300 * 3 hrs = 900. With 1.2x multiplier = 1080.
        $this->assertEquals(1080.0, (float) $booking->price);
    }

    public function test_worker_can_start_and_end_on_site_timer(): void
    {
        $booking = Booking::create([
            'client_id'        => $this->client->id,
            'worker_id'        => $this->worker->id,
            'service_category' => 'Plumbing',
            'scheduled_at'     => now()->addHour(),
            'address'          => 'Tuy Plaza',
            'barangay'         => 'Brgy. Poblacion',
            'status'           => Booking::STATUS_ACCEPTED,
            'price'            => 500,
        ]);

        // Worker starts timer
        $startResponse = $this->actingAs($this->worker)
            ->postJson(route('worker.jobs.start-timer', $booking));

        $startResponse->assertOk()
            ->assertJson(['success' => true]);

        $booking->refresh();
        $this->assertNotNull($booking->work_started_at);
        $this->assertEquals(Booking::STATUS_IN_PROGRESS, $booking->status);

        // Worker ends timer
        $endResponse = $this->actingAs($this->worker)
            ->postJson(route('worker.jobs.end-timer', $booking));

        $endResponse->assertOk()
            ->assertJson(['success' => true]);

        $booking->refresh();
        $this->assertNotNull($booking->work_ended_at);
    }

    public function test_worker_can_request_scope_amendment_and_client_can_approve(): void
    {
        $booking = Booking::create([
            'client_id'        => $this->client->id,
            'worker_id'        => $this->worker->id,
            'service_category' => 'Carpentry',
            'scheduled_at'     => now()->addHour(),
            'address'          => 'Tuy Market',
            'barangay'         => 'Brgy. Poblacion',
            'status'           => Booking::STATUS_IN_PROGRESS,
            'price'            => 600,
        ]);

        // Worker requests amendment
        $amendResponse = $this->actingAs($this->worker)
            ->postJson(route('worker.jobs.request-scope-amendment', $booking), [
                'amendment_price' => 950.00,
                'notes'           => 'Termite damage found inside wall studs requiring structural replacement',
            ]);

        $amendResponse->assertOk()
            ->assertJson(['success' => true]);

        $booking->refresh();
        $this->assertEquals('pending', $booking->scope_amendment_status);
        $this->assertEquals(950.00, (float) $booking->scope_amendment_price);

        // Client approves amendment
        $clientResponse = $this->actingAs($this->client)
            ->postJson(route('client.bookings.respond-scope-amendment', $booking), [
                'action' => 'approve',
            ]);

        $clientResponse->assertOk()
            ->assertJson(['success' => true]);

        $booking->refresh();
        $this->assertEquals('approved', $booking->scope_amendment_status);
        $this->assertEquals(950.00, (float) $booking->price);
    }

    public function test_worker_profile_updates_equipped_tools_and_certifications(): void
    {
        $response = $this->actingAs($this->worker)->put(route('worker.profile.update'), [
            'first_name'                 => $this->worker->first_name,
            'last_name'                  => $this->worker->last_name,
            'language'                   => 'English',
            'phone'                      => '09123456789',
            'city'                       => 'Tuy, Batangas',
            'tools_equipped'             => ['Thermal Camera', 'Digital Multimeter', 'Pipe Threader'],
            'tesda_certified'            => true,
            'barangay_clearance_verified'=> true,
            'min_notice_hours'           => 3,
            'emergency_available'        => true,
        ]);

        $response->assertRedirect();

        $profile = $this->worker->fresh()->workerProfile;
        $this->assertIsArray($profile->tools_equipped);
        $this->assertContains('Digital Multimeter', $profile->tools_equipped);
        $this->assertTrue($profile->tesda_certified);
        $this->assertTrue($profile->barangay_clearance_verified);
        $this->assertEquals(3, $profile->min_notice_hours);
        $this->assertTrue($profile->emergency_available);
    }

    public function test_worker_schedule_page_loads_successfully(): void
    {
        // Create an available peer worker
        $peer = User::factory()->create(['role' => 'worker']);
        WorkerProfile::create(['user_id' => $peer->id]);

        $response = $this->actingAs($this->worker)->get(route('worker.schedule'));

        $response->assertOk()
            ->assertViewIs('worker.schedule.index')
            ->assertViewHas('availablePeers')
            ->assertViewHas('todayUtilization');
    }

    public function test_worker_schedule_page_loads_for_off_duty_worker(): void
    {
        $offDutyWorker = User::factory()->create(['role' => 'worker']);
        WorkerProfile::create([
            'user_id' => $offDutyWorker->id,
            'availability' => null,
        ]);

        $response = $this->actingAs($offDutyWorker)->get(route('worker.schedule'));

        $response->assertOk()
            ->assertViewIs('worker.schedule.index')
            ->assertSee('Off Duty');
    }
}
