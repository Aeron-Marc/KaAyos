<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BookingWorker;
use App\Models\User;
use App\Models\WorkerProfile;
use App\Services\ScheduleMaximizationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase3PeerCrewAndScheduleMaximizationTest extends TestCase
{
    use RefreshDatabase;

    protected User $client;
    protected User $leadWorker;
    protected User $peerWorker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = User::factory()->create(['role' => 'client']);
        $this->leadWorker = User::factory()->create(['role' => 'worker']);
        $this->peerWorker = User::factory()->create(['role' => 'worker']);

        WorkerProfile::create([
            'user_id' => $this->leadWorker->id,
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

        WorkerProfile::create([
            'user_id' => $this->peerWorker->id,
        ]);
    }

    public function test_worker_can_suggest_team_client_approves_and_peer_accepts(): void
    {
        $booking = Booking::create([
            'client_id'        => $this->client->id,
            'worker_id'        => $this->leadWorker->id,
            'service_category' => 'Roofing & Waterproofing',
            'scheduled_at'     => now()->addDay(),
            'address'          => 'Tuy Poblacion',
            'barangay'         => 'Brgy. Poblacion',
            'status'           => Booking::STATUS_ACCEPTED,
            'price'            => 3500,
        ]);

        // 1. Lead worker suggests peer team
        $suggestResponse = $this->actingAs($this->leadWorker)
            ->postJson(route('worker.jobs.suggest-team', $booking), [
                'peer_ids'      => [$this->peerWorker->id],
                'roles'         => ['Roof Safety Assistant'],
                'payouts'       => [1200.00],
                'justification' => 'Second technician required for multi-story harness tethering and torch welding',
            ]);

        $suggestResponse->assertOk()
            ->assertJson(['success' => true]);

        $booking->refresh();
        $this->assertEquals('suggested', $booking->team_status);

        $crewMember = BookingWorker::where('booking_id', $booking->id)
            ->where('worker_id', $this->peerWorker->id)
            ->first();

        $this->assertNotNull($crewMember);
        $this->assertEquals('pending_client_approval', $crewMember->status);
        $this->assertEquals(1200.00, (float) $crewMember->payout_amount);

        // 2. Client approves recommended team
        $approveResponse = $this->actingAs($this->client)
            ->postJson(route('client.bookings.respond-team-suggestion', $booking), [
                'action' => 'approve',
            ]);

        $approveResponse->assertOk()
            ->assertJson(['success' => true]);

        $booking->refresh();
        $this->assertEquals('client_approved', $booking->team_status);

        $crewMember->refresh();
        $this->assertEquals('invited', $crewMember->status);

        // 3. Peer worker accepts crew invitation
        $peerResponse = $this->actingAs($this->peerWorker)
            ->postJson(route('worker.jobs.respond-peer-invite', $booking), [
                'action' => 'accept',
            ]);

        $peerResponse->assertOk()
            ->assertJson(['success' => true, 'status' => 'accepted']);

        $crewMember->refresh();
        $this->assertEquals('accepted', $crewMember->status);
    }

    public function test_client_can_decline_team_recommendation(): void
    {
        $booking = Booking::create([
            'client_id'        => $this->client->id,
            'worker_id'        => $this->leadWorker->id,
            'service_category' => 'Masonry',
            'scheduled_at'     => now()->addDay(),
            'address'          => 'Tuy Center',
            'status'           => Booking::STATUS_ACCEPTED,
            'price'            => 2000,
        ]);

        $this->actingAs($this->leadWorker)
            ->postJson(route('worker.jobs.suggest-team', $booking), [
                'peer_ids'      => [$this->peerWorker->id],
                'roles'         => ['Mixer Assistant'],
                'payouts'       => [600.00],
                'justification' => 'Heavy bag lifting',
            ]);

        $declineResponse = $this->actingAs($this->client)
            ->postJson(route('client.bookings.respond-team-suggestion', $booking), [
                'action' => 'decline',
            ]);

        $declineResponse->assertOk()
            ->assertJson(['success' => true]);

        $booking->refresh();
        $this->assertEquals('client_declined', $booking->team_status);

        $crewMember = BookingWorker::where('booking_id', $booking->id)
            ->where('worker_id', $this->peerWorker->id)
            ->first();

        $this->assertEquals('declined', $crewMember->status);
    }

    public function test_schedule_maximization_computes_utilization_and_detects_gap_slots(): void
    {
        $testDate = Carbon::parse('next Monday');

        // Job 1: 8:00 AM to 10:00 AM (2 hours)
        Booking::create([
            'client_id'               => $this->client->id,
            'worker_id'               => $this->leadWorker->id,
            'service_category'        => 'Plumbing',
            'scheduled_at'            => $testDate->copy()->setHour(8)->setMinute(0),
            'address'                 => 'Brgy. Bayanan, Tuy',
            'barangay'                => 'Brgy. Bayanan',
            'status'                  => Booking::STATUS_ACCEPTED,
            'estimated_duration_hours'=> 2.0,
            'price'                   => 600,
        ]);

        // Job 2: 1:00 PM to 3:00 PM (2 hours) - Leaves 3 hour gap (10:00 AM to 1:00 PM)
        Booking::create([
            'client_id'               => $this->client->id,
            'worker_id'               => $this->leadWorker->id,
            'service_category'        => 'Electrical',
            'scheduled_at'            => $testDate->copy()->setHour(13)->setMinute(0),
            'address'                 => 'Brgy. Luna, Tuy',
            'barangay'                => 'Brgy. Luna',
            'status'                  => Booking::STATUS_ACCEPTED,
            'estimated_duration_hours'=> 2.0,
            'price'                   => 800,
        ]);

        $maximizer = app(ScheduleMaximizationService::class);
        $utilization = $maximizer->computeDailyUtilization($this->leadWorker, $testDate);

        $this->assertIsArray($utilization);
        $this->assertGreaterThan(0, $utilization['utilization_percent']);
        $this->assertEquals(2, $utilization['total_jobs']);
        $this->assertNotEmpty($utilization['gaps']);

        // Check the detected express gap slot
        $gap = $utilization['gaps'][0];
        $this->assertEquals('express_gap_slot', $gap['type']);
        $this->assertGreaterThanOrEqual(90, $gap['usable_minutes']);
        $this->assertEquals('10:00 AM', $gap['start']);
        $this->assertEquals('1:00 PM', $gap['end']);
    }

    public function test_worker_person_a_can_suggest_multiple_peers_person_b_and_c_simultaneously(): void
    {
        $peerWorkerC = User::factory()->create([
            'role' => 'worker',
            'first_name' => 'Carlos',
            'last_name' => 'Mendoza',
            'name' => 'Carlos Mendoza',
            'service_category' => 'Carpentry',
        ]);
        WorkerProfile::create(['user_id' => $peerWorkerC->id]);

        $booking = Booking::create([
            'client_id'        => $this->client->id,
            'worker_id'        => $this->leadWorker->id,
            'service_category' => 'Home Renovation',
            'scheduled_at'     => now()->addDay(),
            'address'          => 'Tuy Poblacion',
            'barangay'         => 'Brgy. Poblacion',
            'status'           => Booking::STATUS_ACCEPTED,
            'price'            => 8000,
        ]);

        // Person A suggests Person B and Person C simultaneously
        $response = $this->actingAs($this->leadWorker)
            ->postJson(route('worker.jobs.suggest-team', $booking), [
                'peer_ids'      => [$this->peerWorker->id, $peerWorkerC->id],
                'roles'         => ['Electrical Lead', 'Framing Specialist'],
                'payouts'       => [2500.00, 2200.00],
                'justification' => 'Renovation requires concurrent framing and wiring inspection team',
            ]);

        $response->assertOk()
            ->assertJson(['success' => true]);

        $booking->refresh();
        $this->assertEquals('suggested', $booking->team_status);

        $crew = BookingWorker::where('booking_id', $booking->id)->get();
        $this->assertCount(2, $crew);

        $crewB = $crew->firstWhere('worker_id', $this->peerWorker->id);
        $this->assertNotNull($crewB);
        $this->assertEquals('Electrical Lead', $crewB->role);
        $this->assertEquals(2500.00, (float) $crewB->payout_amount);
        $this->assertEquals('pending_client_approval', $crewB->status);

        $crewC = $crew->firstWhere('worker_id', $peerWorkerC->id);
        $this->assertNotNull($crewC);
        $this->assertEquals('Framing Specialist', $crewC->role);
        $this->assertEquals(2200.00, (float) $crewC->payout_amount);
        $this->assertEquals('pending_client_approval', $crewC->status);

        // Client approves both recommended peers
        $this->actingAs($this->client)
            ->postJson(route('client.bookings.respond-team-suggestion', $booking), [
                'action' => 'approve',
            ])
            ->assertOk();

        $crewB->refresh();
        $crewC->refresh();
        $this->assertEquals('invited', $crewB->status);
        $this->assertEquals('invited', $crewC->status);
    }

    public function test_worker_can_endorse_recommended_peers_on_profile_and_client_sees_them(): void
    {
        $peerWorkerB = $this->peerWorker;
        $peerWorkerB->update([
            'first_name' => 'Benny',
            'last_name'  => 'Reyes',
            'name'       => 'Benny Reyes',
            'service_category' => 'Electrical',
        ]);

        // Person A endorses Person B on their profile
        $response = $this->actingAs($this->leadWorker)
            ->put(route('worker.profile.update'), [
                'first_name' => 'Arnel',
                'last_name'  => 'Dalisay',
                'language'   => 'English',
                'recommended_peers' => [
                    [
                        'worker_id' => $peerWorkerB->id,
                        'note'      => 'Outstanding certified electrician, partner on multi-circuit rewires.',
                    ],
                ],
            ]);

        $response->assertRedirect(route('worker.profile'));

        $leadProfile = $this->leadWorker->fresh()->workerProfile;
        $this->assertNotNull($leadProfile->recommended_peers);
        $this->assertCount(1, $leadProfile->recommended_peers);
        $this->assertEquals($peerWorkerB->id, $leadProfile->recommended_peers[0]['worker_id']);

        // Check recommended peers details resolver
        $details = $leadProfile->recommended_peers_details;
        $this->assertCount(1, $details);
        $this->assertEquals('Benny Reyes', $details[0]['name']);
        $this->assertEquals('Outstanding certified electrician, partner on multi-circuit rewires.', $details[0]['note']);

        // Client views Person A's public profile and sees Person B recommended
        $clientView = $this->actingAs($this->client)
            ->get(route('client.workers.show', $this->leadWorker));

        $clientView->assertOk();
        $clientView->assertSee('Trusted Peers Recommended by');
        $clientView->assertSee('Benny Reyes');
        $clientView->assertSee('Outstanding certified electrician, partner on multi-circuit rewires.');
    }

    public function test_cross_midnight_availability_computes_correct_capacity(): void
    {
        // Set worker availability: 22:00 – 02:00 (cross-midnight = 4h capacity)
        $this->leadWorker->workerProfile->update([
            'availability' => [
                ['day' => 'Monday',    'active' => true,  'start' => '22:00', 'end' => '02:00'],
                ['day' => 'Tuesday',   'active' => true,  'start' => '22:00', 'end' => '02:00'],
                ['day' => 'Wednesday', 'active' => true,  'start' => '22:00', 'end' => '02:00'],
                ['day' => 'Thursday',  'active' => true,  'start' => '22:00', 'end' => '02:00'],
                ['day' => 'Friday',    'active' => true,  'start' => '22:00', 'end' => '02:00'],
                ['day' => 'Saturday',  'active' => true,  'start' => '22:00', 'end' => '02:00'],
                ['day' => 'Sunday',    'active' => true,  'start' => '22:00', 'end' => '02:00'],
            ],
        ]);

        $service = app(ScheduleMaximizationService::class);

        // Use a known Monday
        $monday = Carbon::parse('next Monday');
        $result = $service->computeDailyUtilization($this->leadWorker->fresh(), $monday);

        // 22:00 to 02:00 next day = 240 minutes = 4 hours
        $this->assertEquals(4.0, $result['total_capacity_hours'],
            'Cross-midnight window 22:00–02:00 should produce 4h capacity');
        $this->assertStringContainsString('10:00 PM – 2:00 AM', $result['availability_window'],
            'Availability window should display formatted 12-hour times');
        $this->assertStringContainsString('+1 day', $result['availability_window'],
            'Cross-midnight availability window label should indicate next day');
    }

    public function test_custom_availability_hours_compute_correct_capacity(): void
    {
        // Worker available 18:00 – 23:00 (same-day evening, no midnight cross)
        $this->leadWorker->workerProfile->update([
            'availability' => [
                ['day' => 'Monday', 'active' => true, 'start' => '18:00', 'end' => '23:00'],
            ],
        ]);

        $service = app(ScheduleMaximizationService::class);
        $monday  = Carbon::parse('next Monday');
        $result  = $service->computeDailyUtilization($this->leadWorker->fresh(), $monday);

        // 18:00–23:00 = 5h
        $this->assertEquals(5.0, $result['total_capacity_hours'],
            'Availability window 18:00–23:00 should produce 5h capacity');
        $this->assertStringContainsString('6:00 PM – 11:00 PM', $result['availability_window'],
            'Availability window should display formatted 12-hour times');
        $this->assertStringNotContainsString('+1 day', $result['availability_window'],
            'Same-day availability window should not contain +1 day');
    }
}


