<?php

namespace Tests\Feature;

use App\Models\ClientIdentityVerification;
use App\Models\User;
use App\Models\WorkerProfile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ClientIdentityVerificationTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    protected User $client;
    protected User $admin;
    protected User $worker;
    protected User $otherClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client      = User::factory()->create(['role' => 'client']);
        $this->admin       = User::factory()->create(['role' => 'admin']);
        $this->worker      = User::factory()->create(['role' => 'worker']);
        $this->otherClient = User::factory()->create(['role' => 'client']);
    }

    protected function eligibleWorker(): void
    {
        WorkerProfile::create([
            'user_id'      => $this->worker->id,
            'availability' => [
                ['day' => 'Monday', 'active' => true, 'start' => '08:00', 'end' => '17:00'],
                ['day' => 'Tuesday', 'active' => true, 'start' => '08:00', 'end' => '17:00'],
                ['day' => 'Wednesday', 'active' => true, 'start' => '08:00', 'end' => '17:00'],
                ['day' => 'Thursday', 'active' => true, 'start' => '08:00', 'end' => '17:00'],
                ['day' => 'Friday', 'active' => true, 'start' => '08:00', 'end' => '17:00'],
            ],
        ]);
    }

    protected function validBookingData(): array
    {
        return [
            'worker_id'        => $this->worker->id,
            'service_category' => 'Plumbing',
            'scheduled_at'     => now()->addDay()->format('Y-m-d H:i:s'),
            'house_no'         => '123',
            'barangay'         => 'Luna',
            'price'            => 500.00,
        ];
    }

    protected function submitIdentity(array $overrides = [])
    {
        Storage::fake('identity');

        return $this->actingAs($this->client)->post(route('client.account.identity.store'), array_merge([
            'id_type'      => 'philsys',
            'id'           => UploadedFile::fake()->image('id_card.jpg'),
            'selfie'       => UploadedFile::fake()->image('selfie.jpg', 600, 600),
            'consent'      => '1',
            'age_attested' => '1',
        ], $overrides));
    }

    /** ---- Submission ---- */

    public function test_valid_id_and_selfie_creates_pending_submission(): void
    {
        $response = $this->submitIdentity();

        $response->assertRedirect(route('client.account.identity'));

        $this->assertDatabaseHas('client_identity_verifications', [
            'user_id'   => $this->client->id,
            'status'    => 'pending',
            'id_type'   => 'philsys',
        ]);

        $response = $this->actingAs($this->client)->get(route('client.account.identity'));
        $response->assertOk();
        $response->assertSee('Pending');

        $this->assertDatabaseHas('client_identity_verifications', [
            'user_id'   => $this->client->id,
            'status'    => 'pending',
            'id_type'   => 'philsys',
        ]);

        $verification = ClientIdentityVerification::where('user_id', $this->client->id)->first();

        $this->assertNotNull($verification->id_document_path);
        $this->assertNotNull($verification->selfie_path);
        $this->assertTrue(Storage::disk('identity')->exists($verification->id_document_path));
        $this->assertTrue(Storage::disk('identity')->exists($verification->selfie_path));

        // Original filename never used; path is {userId}/{uuid}.{mime-ext}
        $this->assertStringContainsString($this->client->id.'/', $verification->id_document_path);
        $this->assertStringNotContainsString('id_card', $verification->id_document_path);
        $this->assertStringNotContainsString('selfie', $verification->selfie_path);

        // Consent + age attestation recorded
        $this->assertNotNull($verification->consent_at);
        $this->assertSame('2026-10-28', $verification->consent_version);
        $this->assertNotNull($verification->age_attested_at);
    }

    public function test_rejected_id_extension_is_not_stored(): void
    {
        $this->submitIdentity(['id' => UploadedFile::fake()->create('id.exe', 100)]);

        $response = $this->actingAs($this->client)->post(route('client.account.identity.store'), [
            'id_type'      => 'philsys',
            'id'           => UploadedFile::fake()->create('id.exe', 100),
            'selfie'       => UploadedFile::fake()->image('selfie.jpg'),
            'consent'      => '1',
            'age_attested' => '1',
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseCount('client_identity_verifications', 0);
        $this->assertFalse(Storage::disk('identity')->exists('whatever'));
    }

    public function test_pdf_selfie_is_rejected(): void
    {
        Storage::fake('identity');

        $this->actingAs($this->client)->post(route('client.account.identity.store'), [
            'id_type'      => 'philsys',
            'id'           => UploadedFile::fake()->image('id_card.jpg'),
            'selfie'       => UploadedFile::fake()->create('selfie.pdf', 100),
            'consent'      => '1',
            'age_attested' => '1',
        ])->assertStatus(302);

        $this->assertDatabaseCount('client_identity_verifications', 0);
    }

    public function test_oversized_file_is_rejected(): void
    {
        Storage::fake('identity');

        $this->actingAs($this->client)->post(route('client.account.identity.store'), [
            'id_type'      => 'philsys',
            'id'           => UploadedFile::fake()->create('id.jpg', 6 * 1024 * 1024),
            'selfie'       => UploadedFile::fake()->image('selfie.jpg'),
            'consent'      => '1',
            'age_attested' => '1',
        ])->assertStatus(302);

        $this->assertDatabaseCount('client_identity_verifications', 0);
    }

    public function test_client_without_identity_cannot_access_admin_review(): void
    {
        $this->submitIdentity();

        $verification = ClientIdentityVerification::where('user_id', $this->client->id)->first();

        $this->actingAs($this->client)
            ->post(route('admin.client-verifications.approve', $verification))
            ->assertForbidden();

        $this->actingAs($this->client)
            ->post(route('admin.client-verifications.reject', $verification))
            ->assertForbidden();
    }

    public function test_pending_blocks_resubmission(): void
    {
        $this->submitIdentity();

        $this->submitIdentity();

        $this->assertDatabaseCount('client_identity_verifications', 1);
    }

    public function test_rejected_and_resubmission_required_allow_resubmit(): void
    {
        $this->submitIdentity();
        $verification = ClientIdentityVerification::where('user_id', $this->client->id)->first();

        // Admin rejects
        $this->actingAs($this->admin)
            ->post(route('admin.client-verifications.reject', $verification), [
                'rejection_reason' => 'Blurry document.',
            ]);

        $verification->refresh();
        $this->assertSame('rejected', $verification->status);

        sleep(1);

        // Client resubmits
        $this->submitIdentity();

        $this->assertDatabaseCount('client_identity_verifications', 2);
        $latest = ClientIdentityVerification::where('user_id', $this->client->id)
            ->latest('submitted_at')->first();
        $this->assertSame('pending', $latest->status);
        $this->assertNotSame($verification->id_document_path, $latest->id_document_path);
    }

    public function test_resubmission_required_allows_resubmit(): void
    {
        $this->submitIdentity();
        $verification = ClientIdentityVerification::where('user_id', $this->client->id)->first();

        $this->actingAs($this->admin)
            ->post(route('admin.client-verifications.request-resubmission', $verification), [
                'rejection_reason' => 'Please retake the selfie.',
            ]);

        $verification->refresh();
        $this->assertSame('resubmission_required', $verification->status);

        $this->submitIdentity();
        $this->assertDatabaseCount('client_identity_verifications', 2);
    }

    /** ---- Admin review ---- */

    public function test_admin_can_approve_and_reject(): void
    {
        Notification::fake();

        $this->submitIdentity();
        $verification = ClientIdentityVerification::where('user_id', $this->client->id)->first();

        $this->actingAs($this->admin)
            ->post(route('admin.client-verifications.approve', $verification), [
                'notes' => 'Looks good.',
            ])
            ->assertRedirect();

        $verification->refresh();
        $this->assertSame('verified', $verification->status);
        $this->assertNotNull($verification->reviewed_by);
        $this->assertNotNull($verification->reviewed_at);

        Notification::assertSentTo($this->client, \App\Notifications\ClientIdentityApproved::class);
    }

    public function test_admin_can_request_resubmission(): void
    {
        Notification::fake();

        $this->submitIdentity();
        $verification = ClientIdentityVerification::where('user_id', $this->client->id)->first();

        $this->actingAs($this->admin)
            ->post(route('admin.client-verifications.request-resubmission', $verification), [
                'rejection_reason' => 'Retake selfie.',
            ])
            ->assertRedirect();

        $verification->refresh();
        $this->assertSame('resubmission_required', $verification->status);
        Notification::assertSentTo($this->client, \App\Notifications\ClientIdentityResubmissionRequested::class);
    }

    public function test_double_review_is_blocked(): void
    {
        $this->submitIdentity();
        $verification = ClientIdentityVerification::where('user_id', $this->client->id)->first();

        $this->actingAs($this->admin)
            ->post(route('admin.client-verifications.reject', $verification), [
                'rejection_reason' => 'No.',
            ]);

        $verification->refresh();
        $this->assertSame('rejected', $verification->status);

        // Second review attempt on a non-pending submission is blocked.
        $this->actingAs($this->admin)
            ->post(route('admin.client-verifications.reject', $verification), [
                'rejection_reason' => 'Again.',
            ])
            ->assertSessionHas('error');

        $verification->refresh();
        $this->assertSame('rejected', $verification->status);
    }

    /** ---- Document access ---- */

    public function test_admin_can_stream_document(): void
    {
        $this->submitIdentity();
        $verification = ClientIdentityVerification::where('user_id', $this->client->id)->first();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.client-verifications.document', [$verification, 'id']));

        $response->assertOk();
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeaderContains('Cache-Control', 'no-store');
    }

    public function test_other_client_cannot_access_document(): void
    {
        $this->submitIdentity();
        $verification = ClientIdentityVerification::where('user_id', $this->client->id)->first();

        $this->actingAs($this->otherClient)
            ->get(route('admin.client-verifications.document', [$verification, 'id']))
            ->assertForbidden();
    }

    public function test_worker_cannot_access_document(): void
    {
        $this->submitIdentity();
        $verification = ClientIdentityVerification::where('user_id', $this->client->id)->first();

        $this->actingAs($this->worker)
            ->get(route('admin.client-verifications.document', [$verification, 'id']))
            ->assertForbidden();
    }

    public function test_guest_cannot_access_document(): void
    {
        $verification = ClientIdentityVerification::create([
            'user_id'           => $this->client->id,
            'id_type'           => 'philsys',
            'id_document_path'  => 'clients/1/id.jpg',
            'selfie_path'       => 'clients/1/selfie.jpg',
            'status'            => 'pending',
        ]);

        $this->get(route('admin.client-verifications.document', [$verification, 'id']))
            ->assertRedirect();
    }

    public function test_invalid_document_parameter_is_not_found(): void
    {
        $this->submitIdentity();
        $verification = ClientIdentityVerification::where('user_id', $this->client->id)->first();

        $this->actingAs($this->admin)
            ->get(route('admin.client-verifications.document', [$verification, '..%2F..%2Fetc%2Fpasswd']))
            ->assertNotFound();
    }

    /** ---- Booking gate ---- */

    public function test_unverified_client_booking_is_rejected_with_no_row(): void
    {
        $this->eligibleWorker();
        config(['kaayos.client_identity.enforced' => true]);

        $before = \App\Models\Booking::count();

        $response = $this->actingAs($this->client)
            ->postJson(route('client.bookings.store'), $this->validBookingData());

        $response->assertStatus(422)
            ->assertJsonPath('code', 'identity_verification_required')
            ->assertJsonPath('redirect', route('client.account.identity'));

        $this->assertSame($before, \App\Models\Booking::count());
    }

    public function test_verified_client_can_book(): void
    {
        $this->eligibleWorker();

        $verification = ClientIdentityVerification::create([
            'user_id'           => $this->client->id,
            'id_type'           => 'philsys',
            'id_document_path'  => 'clients/1/id.jpg',
            'selfie_path'       => 'clients/1/selfie.jpg',
            'status'            => 'pending',
            'submitted_at'      => now(),
            'reviewed_at'       => now(),
            'consent_at'        => now(),
            'consent_version'   => '2026-10-28',
            'age_attested_at'   => now(),
        ]);
        $verification->status = 'verified';
        $verification->save();

        $response = $this->actingAs($this->client)
            ->postJson(route('client.bookings.store'), $this->validBookingData());

        $response->assertOk()->assertJsonPath('success', true);
        $this->assertDatabaseHas('bookings', ['client_id' => $this->client->id]);
    }

    public function test_enforced_false_bypasses_identity_check(): void
    {
        $this->eligibleWorker();
        config(['kaayos.client_identity.enforced' => false]);

        $response = $this->actingAs($this->client)
            ->postJson(route('client.bookings.store'), $this->validBookingData());

        $response->assertOk()->assertJsonPath('success', true);
        $this->assertDatabaseHas('bookings', ['client_id' => $this->client->id]);
    }

    public function test_rejected_client_booking_is_rejected(): void
    {
        $this->eligibleWorker();

        $verification = ClientIdentityVerification::create([
            'user_id'           => $this->client->id,
            'id_type'           => 'philsys',
            'id_document_path'  => 'clients/1/id.jpg',
            'selfie_path'       => 'clients/1/selfie.jpg',
            'status'            => 'pending',
            'submitted_at'      => now(),
            'reviewed_at'       => now(),
        ]);
        $verification->status = 'rejected';
        $verification->save();

        $response = $this->actingAs($this->client)
            ->postJson(route('client.bookings.store'), $this->validBookingData());

        $response->assertStatus(422)
            ->assertJsonPath('code', 'identity_verification_required');

        $this->assertDatabaseCount('bookings', 0);
    }

    /** ---- Model helpers ---- */

    public function test_client_identity_status_helpers(): void
    {
        $this->assertSame('not_submitted', $this->client->clientIdentityStatus());
        $this->assertFalse($this->client->isClientIdentityVerified());

        $this->submitIdentity();
        $this->assertSame('pending', $this->client->fresh()->clientIdentityStatus());
        $this->assertFalse($this->client->fresh()->isClientIdentityVerified());
    }

    public function test_latest_of_many_returns_latest_submission(): void
    {
        $this->submitIdentity();
        $verification = ClientIdentityVerification::where('user_id', $this->client->id)->first();

        $this->actingAs($this->admin)
            ->post(route('admin.client-verifications.reject', $verification), [
                'rejection_reason' => 'Needs a clearer photo.',
            ]);

        sleep(1);

        $this->submitIdentity(['id_type' => 'passport']);

        $latest = $this->client->fresh()->latestClientIdentityVerification;
        $this->assertSame('passport', $latest->id_type);
    }
}
