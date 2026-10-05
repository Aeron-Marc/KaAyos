<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use App\Models\WorkerDocument;
use App\Models\WorkerProfile;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class Phase1QuickFixesTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    protected User $client;
    protected User $worker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = User::factory()->create(['role' => 'client']);
        $this->worker = User::factory()->create(['role' => 'worker']);
        $this->createWorkerProfile($this->worker);
    }

    protected function createWorkerProfile(User $worker): void
    {
        WorkerProfile::create([
            'user_id'      => $worker->id,
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

    protected function createBooking(string $status, array $overrides = []): Booking
    {
        return Booking::create(array_merge([
            'client_id'        => $this->client->id,
            'worker_id'        => $this->worker->id,
            'service_category' => 'Plumbing',
            'scheduled_at'     => now()->subDay(),
            'address'          => '123, Brgy. Bayanan, Tuy, Batangas',
            'house_no'         => '123',
            'barangay'         => 'Brgy. Bayanan',
            'status'           => $status,
            'completed_at'     => $status === Booking::STATUS_COMPLETED ? now() : null,
            'price'            => 500.00,
        ], $overrides));
    }

    // Feature 1 — Anonymous reviews

    public function test_client_can_submit_anonymous_review(): void
    {
        $booking = $this->createBooking(Booking::STATUS_COMPLETED);

        $this->actingAs($this->client)
            ->postJson(route('client.bookings.review', $booking), [
                'rating'       => 5,
                'comment'      => 'Great work!',
                'is_anonymous' => 1,
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('reviews', [
            'booking_id'   => $booking->id,
            'client_id'    => $this->client->id,
            'is_anonymous' => 1,
        ]);
    }

    public function test_review_defaults_to_named_when_anonymous_flag_absent(): void
    {
        $booking = $this->createBooking(Booking::STATUS_COMPLETED);

        $this->actingAs($this->client)
            ->postJson(route('client.bookings.review', $booking), [
                'rating'  => 4,
                'comment' => 'Solid job.',
            ])
            ->assertOk();

        $this->assertDatabaseHas('reviews', [
            'booking_id'   => $booking->id,
            'is_anonymous' => 0,
        ]);
    }

    public function test_public_profile_masks_anonymous_reviewer_name(): void
    {
        $booking = $this->createBooking(Booking::STATUS_COMPLETED);

        $this->actingAs($this->client)
            ->postJson(route('client.bookings.review', $booking), [
                'rating'       => 5,
                'comment'      => 'Great work!',
                'is_anonymous' => 1,
            ])
            ->assertOk();

        $response = $this->get(route('workers.public.show', $this->worker));

        $response->assertOk();
        $response->assertSee('Anonymous', false);
        $response->assertSee('Great work!', false);
        $response->assertDontSee($this->client->name, false);
    }

    public function test_public_profile_shows_client_name_for_named_review(): void
    {
        $booking = $this->createBooking(Booking::STATUS_COMPLETED);

        $this->actingAs($this->client)
            ->postJson(route('client.bookings.review', $booking), [
                'rating'  => 5,
                'comment' => 'Great work!',
            ])
            ->assertOk();

        $this->get(route('workers.public.show', $this->worker))
            ->assertOk()
            ->assertSee($this->client->name, false);
    }

    // Feature 3 — Pre-negative-feedback modal

    public function test_review_page_has_negative_feedback_prompt_and_anonymous_toggle(): void
    {
        $response = $this->actingAs($this->client)->get(route('client.reviews'));

        $response->assertOk();
        $response->assertSee('negativeFeedbackModal', false);
        $response->assertSee('is_anonymous', false);
        $response->assertSee("We're sorry it didn't go well", false);
    }

    // Feature 2 — Tips at job completion

    public function test_tip_collected_at_completion_is_paid_to_worker_without_platform_fee(): void
    {
        Notification::fake();

        $booking = $this->createBooking(Booking::STATUS_IN_PROGRESS);

        $this->actingAs($this->worker)
            ->patchJson("/worker/jobs/{$booking->id}/status", [
                'status' => Booking::STATUS_COMPLETED,
            ])
            ->assertOk();

        $this->actingAs($this->client)
            ->postJson("/client/bookings/{$booking->id}/confirm-complete", [
                'tip_amount' => 100,
            ])
            ->assertOk();

        $this->assertDatabaseHas('bookings', [
            'id'          => $booking->id,
            'status'      => Booking::STATUS_COMPLETED,
            'tip_amount'  => 100.00,
        ]);

        $this->assertDatabaseHas('earnings', [
            'booking_id'   => $booking->id,
            'worker_id'    => $this->worker->id,
            'gross_amount' => 500.00,
            'platform_fee' => 50.00,
            'tip_amount'   => 100.00,
            'net_amount'   => 550.00,
        ]);
    }

    public function test_completion_without_tip_records_zero_tip_earning(): void
    {
        Notification::fake();

        $booking = $this->createBooking(Booking::STATUS_IN_PROGRESS);

        $this->actingAs($this->worker)
            ->patchJson("/worker/jobs/{$booking->id}/status", [
                'status' => Booking::STATUS_COMPLETED,
            ])
            ->assertOk();

        $this->actingAs($this->client)
            ->postJson("/client/bookings/{$booking->id}/confirm-complete")
            ->assertOk();

        $this->assertDatabaseHas('earnings', [
            'booking_id'   => $booking->id,
            'gross_amount' => 500.00,
            'platform_fee' => 50.00,
            'tip_amount'   => 0.00,
            'net_amount'   => 450.00,
        ]);
    }

    public function test_invalid_tip_amount_is_rejected(): void
    {
        Notification::fake();

        $booking = $this->createBooking(Booking::STATUS_IN_PROGRESS);

        $this->actingAs($this->worker)
            ->patchJson("/worker/jobs/{$booking->id}/status", [
                'status' => Booking::STATUS_COMPLETED,
            ])
            ->assertOk();

        $this->actingAs($this->client)
            ->postJson("/client/bookings/{$booking->id}/confirm-complete", [
                'tip_amount' => -5,
            ])
            ->assertStatus(422);
    }

    // Feature 5 — Shareable worker ID / QR

    public function test_worker_gets_share_code_and_share_route_resolves(): void
    {
        $code = $this->worker->fresh()->share_code;

        $this->assertNotNull($code);
        $this->assertStringStartsWith('ID-', $code);

        $this->get('/worker/' . $code)
            ->assertOk()
            ->assertSee('Share Profile', false);

        $this->get(route('workers.share', $code))->assertOk();
    }

    public function test_share_route_returns_404_for_unknown_code(): void
    {
        $this->get('/worker/ID-ZZZZZ')->assertNotFound();
    }

    public function test_legacy_worker_id_route_still_works(): void
    {
        $this->get(route('workers.public.show', $this->worker))->assertOk();
    }

    // Feature 4 — Verification status banner

    public function test_pending_document_shows_verification_banner_on_worker_dashboard(): void
    {
        WorkerDocument::create([
            'user_id'       => $this->worker->id,
            'document_type' => 'Government-Issued ID',
            'file_path'      => 'documents/sample.png',
            'status'        => 'pending',
        ]);

        $this->actingAs($this->worker)
            ->get(route('worker.dashboard'))
            ->assertOk()
            ->assertSee('Verification in review', false)
            ->assertSee('business days', false);
    }

    public function test_no_banner_when_documents_are_verified(): void
    {
        WorkerDocument::create([
            'user_id'       => $this->worker->id,
            'document_type' => 'Government-Issued ID',
            'file_path'      => 'documents/sample.png',
            'status'        => 'verified',
            'verified_at'   => now(),
        ]);

        $this->actingAs($this->worker)
            ->get(route('worker.dashboard'))
            ->assertOk()
            ->assertDontSee('Verification in review', false);
    }

    // Feature 6 — Terms, Regulations & Guidelines

    public function test_terms_page_renders_as_full_public_page(): void
    {
        $response = $this->get('/terms');

        $response->assertOk();
        $response->assertSee('Terms, Regulations', false);
        $response->assertSee('Platform Rules', false);
        $response->assertSee('Worker Verification', false);
        $response->assertSee('Enforcement', false);
    }

    public function test_terms_route_uses_page_controller(): void
    {
        $response = $this->get(route('terms'));

        $response->assertOk();
        $response->assertViewIs('pages.terms');
    }
}
