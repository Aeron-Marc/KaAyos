<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Dispute;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CounterClaimTest extends TestCase
{
    use RefreshDatabase;

    protected User $worker;
    protected User $otherWorker;
    protected User $client;
    protected User $admin;
    protected Booking $booking;
    protected Dispute $dispute;

    protected function setUp(): void
    {
        parent::setUp();

        $this->worker = User::factory()->create(['role' => 'worker']);
        $this->otherWorker = User::factory()->create(['role' => 'worker']);
        $this->client = User::factory()->create(['role' => 'client']);
        $this->admin = User::factory()->create(['role' => 'admin']);

        $this->booking = Booking::create([
            'client_id'        => $this->client->id,
            'worker_id'        => $this->worker->id,
            'status'           => Booking::STATUS_COMPLETED,
            'service_category' => 'Plumbing',
            'scheduled_at'     => now()->subDay(),
            'address'          => '123 Brgy. Bayanan, Tuy, Batangas',
            'house_no'         => '123',
            'barangay'         => 'Brgy. Bayanan',
            'price'            => 500,
        ]);

        $this->dispute = Dispute::create([
            'type'               => 'worker_report',
            'booking_id'         => $this->booking->id,
            'raised_by'          => $this->client->id,
            'reported_worker_id' => $this->worker->id,
            'status'             => 'open',
            'reason'             => 'The worker damaged my sink during the repair.',
        ]);
    }

    public function test_worker_claims_page_lists_own_claims(): void
    {
        $this->actingAs($this->worker)
            ->get('/worker/claims')
            ->assertOk()
            ->assertSee('Claims Against You')
            ->assertSee('The worker damaged my sink during the repair.')
            ->assertSee('Submit counter-claim');
    }

    public function test_claims_page_excludes_other_workers_claims(): void
    {
        $otherBooking = Booking::create([
            'client_id'        => $this->client->id,
            'worker_id'        => $this->otherWorker->id,
            'status'           => Booking::STATUS_COMPLETED,
            'service_category' => 'Electrical',
            'scheduled_at'     => now()->subDay(),
            'address'          => '456 Brgy. Luna, Tuy, Batangas',
            'house_no'         => '456',
            'barangay'         => 'Brgy. Luna',
            'price'            => 800,
        ]);

        Dispute::create([
            'type'               => 'worker_report',
            'booking_id'         => $otherBooking->id,
            'raised_by'          => $this->client->id,
            'reported_worker_id' => $this->otherWorker->id,
            'status'             => 'open',
            'reason'             => 'Other worker specific allegation that must not appear.',
        ]);

        $this->actingAs($this->worker)
            ->get('/worker/claims')
            ->assertOk()
            ->assertSee('The worker damaged my sink during the repair.')
            ->assertDontSee('Other worker specific allegation that must not appear.');
    }

    public function test_worker_submits_counter_claim(): void
    {
        $this->actingAs($this->worker)
            ->post(route('worker.claims.counter', $this->dispute), [
                'counter_claim' => 'The sink was already cracked before I arrived; the client confirmed it on chat.',
            ])
            ->assertRedirect();

        $this->dispute->refresh();
        $this->assertNotNull($this->dispute->counter_claim);
        $this->assertNotNull($this->dispute->counter_claim_submitted_at);
        $this->assertStringContainsString('sink was already cracked', $this->dispute->counter_claim);

        $this->actingAs($this->worker)
            ->get('/worker/claims')
            ->assertOk()
            ->assertSee('sink was already cracked')
            ->assertSee('Edit counter-claim');
    }

    public function test_counter_claim_requires_minimum_length(): void
    {
        $this->actingAs($this->worker)
            ->post(route('worker.claims.counter', $this->dispute), [
                'counter_claim' => 'short',
            ])
            ->assertSessionHasErrors('counter_claim');

        $this->dispute->refresh();
        $this->assertNull($this->dispute->counter_claim);
    }

    public function test_worker_cannot_counter_claim_another_workers_dispute(): void
    {
        $this->actingAs($this->otherWorker)
            ->post(route('worker.claims.counter', $this->dispute), [
                'counter_claim' => 'This worker should not be able to file against this dispute.',
            ])
            ->assertStatus(403);

        $this->dispute->refresh();
        $this->assertNull($this->dispute->counter_claim);
    }

    public function test_counter_claim_blocked_when_dispute_is_resolved(): void
    {
        $this->dispute->update(['status' => 'resolved']);

        $this->actingAs($this->worker)
            ->post(route('worker.claims.counter', $this->dispute), [
                'counter_claim' => 'Trying to respond after this dispute was already resolved.',
            ])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->dispute->refresh();
        $this->assertNull($this->dispute->counter_claim);
    }

    public function test_non_worker_cannot_access_claims_page(): void
    {
        $this->actingAs($this->client)
            ->get('/worker/claims')
            ->assertStatus(403);
    }

    public function test_worker_sidebar_links_to_claims(): void
    {
        $this->actingAs($this->worker)
            ->get('/worker/dashboard')
            ->assertOk()
            ->assertSee('/worker/claims');
    }

    public function test_admin_dispute_show_renders_counter_claim_and_suspend_form(): void
    {
        $this->dispute->update([
            'counter_claim'             => 'The scope changed mid-job; the client approved the extra work in chat.',
            'counter_claim_submitted_at' => now(),
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.disputes.show', $this->dispute))
            ->assertOk()
            ->assertSee('Worker Counter-Claim')
            ->assertSee('scope changed mid-job')
            ->assertSee('Worker Actions')
            ->assertSee('Suspend Worker')
            ->assertSee('Non-compliance — Dispute #' . $this->dispute->id);
    }

    public function test_admin_dispute_show_shows_empty_counter_claim_state(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.disputes.show', $this->dispute))
            ->assertOk()
            ->assertSee('No counter-claim submitted yet.');
    }

    public function test_admin_workers_index_shows_suspend_and_reactivate_actions(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.workers.index'))
            ->assertOk()
            ->assertSee('Suspend', false);

        $this->worker->update(['suspended_at' => now(), 'suspended_reason' => 'Prior violation']);

        $this->actingAs($this->admin)
            ->get(route('admin.workers.index'))
            ->assertOk()
            ->assertSee('Reactivate', false);
    }

    public function test_admin_can_suspend_worker_from_dispute_flow(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.users.suspend', $this->worker), [
                'reason' => 'Non-compliance — Dispute #' . $this->dispute->id,
            ])
            ->assertRedirect();

        $this->worker->refresh();
        $this->assertNotNull($this->worker->suspended_at);
        $this->assertStringContainsString('Dispute #' . $this->dispute->id, (string) $this->worker->suspended_reason);
    }
}
