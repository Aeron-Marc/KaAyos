<?php

namespace Tests\Feature\Admin;

use App\Models\Booking;
use App\Models\Dispute;
use App\Models\User;
use App\Models\WorkerDocument;
use App\Services\AdminDashboardMetrics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $client;

    private User $worker;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-10-21 12:00:00');
        Cache::flush();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->client = User::factory()->create(['role' => 'client']);
        $this->worker = User::factory()->create(['role' => 'worker']);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_guest_is_redirected_and_non_admin_is_forbidden(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect();

        $this->actingAs($this->client)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_dashboard_renders_for_admin_and_shares_nav_counts(): void
    {
        Dispute::create([
            'booking_id' => $this->booking(Booking::STATUS_COMPLETED)->id,
            'raised_by' => $this->client->id,
            'reported_worker_id' => $this->worker->id,
            'type' => 'booking_dispute',
            'reason' => 'No-show',
            'status' => 'open',
        ]);

        WorkerDocument::create([
            'user_id' => $this->worker->id,
            'document_type' => 'Government-Issued ID',
            'file_path' => 'documents/id.jpg',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.dashboard', ['period' => '30d']))
            ->assertOk()
            ->assertViewIs('admin.dashboard')
            ->assertViewHas('metrics')
            ->assertViewHas('adminNav');

        $this->assertSame(1, $response->viewData('adminNav')['disputes']);
        $this->assertSame(1, $response->viewData('adminNav')['verifications']);
    }

    public function test_rates_reconcile_and_revenue_separates_tips(): void
    {
        $this->booking(Booking::STATUS_COMPLETED, ['price' => 100, 'tip_amount' => 10]);
        $this->booking(Booking::STATUS_COMPLETED, ['price' => 150, 'tip_amount' => 10]);
        $this->booking(Booking::STATUS_CANCELLED);
        $this->booking(Booking::STATUS_DECLINED);
        $this->booking(Booking::STATUS_ACCEPTED);
        $this->booking(Booking::STATUS_EN_ROUTE);
        $this->booking(Booking::STATUS_IN_PROGRESS);
        $this->booking(Booking::STATUS_NEW);

        $metrics = app(AdminDashboardMetrics::class)->forPeriod('all_time');
        $kpis = $metrics['kpis'];

        $this->assertSame(8, $kpis['total_bookings']);
        $this->assertSame(3, $kpis['active_now']['value']);
        $this->assertSame(1, $kpis['awaiting_acceptance']['value']);
        $this->assertSame(50, $kpis['completion_rate']['value']);
        $this->assertSame(25, $kpis['cancellation_rate']['value']);
        $this->assertSame(25, $kpis['decline_rate']['value']);
        $this->assertSame(4, $kpis['completion_rate']['basis']);
        $this->assertSame(4, $kpis['completion_rate']['completed'] + $kpis['cancellation_rate']['cancelled'] + $kpis['decline_rate']['declined']);
        $this->assertSame(250.0, $kpis['revenue']['value']);
        $this->assertSame(20.0, $kpis['tips']['value']);
        $this->assertSame(270.0, $kpis['gross_collected']['value']);
    }

    public function test_period_outcomes_use_terminal_timestamps_not_created_at(): void
    {
        $this->booking(Booking::STATUS_COMPLETED, [
            'price' => 500,
            'completed_at' => now()->subDays(2),
            'created_at' => now()->subMonths(2),
        ]);

        $this->booking(Booking::STATUS_COMPLETED, [
            'price' => 900,
            'completed_at' => now()->subMonths(2),
            'created_at' => now()->subDays(2),
        ]);

        $kpis = app(AdminDashboardMetrics::class)->forPeriod('7d')['kpis'];

        $this->assertSame(1, $kpis['completed']);
        $this->assertSame(1, $kpis['total_bookings']);
        $this->assertSame(500.0, $kpis['revenue']['value']);
    }

    public function test_unresolved_dispute_filter_matches_dashboard_attention_count(): void
    {
        foreach (['open', 'under_review', 'resolved'] as $status) {
            Dispute::create([
                'booking_id' => $this->booking(Booking::STATUS_COMPLETED)->id,
                'raised_by' => $this->client->id,
                'reported_worker_id' => $this->worker->id,
                'type' => 'booking_dispute',
                'reason' => 'Issue',
                'status' => $status,
                'resolved_at' => $status === 'resolved' ? now() : null,
            ]);
        }

        $response = $this->actingAs($this->admin)
            ->get(route('admin.disputes.index', ['status' => 'unresolved']))
            ->assertOk();

        $this->assertSame(2, $response->viewData('disputes')->total());
        $this->assertSame(2, app(AdminDashboardMetrics::class)->forPeriod('all_time')['attention']['unresolved_disputes']);
    }

    public function test_pending_verification_count_is_distinct_per_worker(): void
    {
        WorkerDocument::create([
            'user_id' => $this->worker->id,
            'document_type' => 'Government-Issued ID',
            'file_path' => 'documents/id.jpg',
            'status' => 'pending',
        ]);
        WorkerDocument::create([
            'user_id' => $this->worker->id,
            'document_type' => 'Barangay Clearance',
            'file_path' => 'documents/clearance.jpg',
            'status' => 'pending',
        ]);

        $otherWorker = User::factory()->create(['role' => 'worker']);
        WorkerDocument::create([
            'user_id' => $otherWorker->id,
            'document_type' => 'Government-Issued ID',
            'file_path' => 'documents/id-2.jpg',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.verification.index', ['status' => 'pending']))
            ->assertOk();

        $this->assertSame(2, $response->viewData('workers')->total());
        $this->assertSame(2, $response->viewData('pendingCount'));
        $this->assertSame(2, app(AdminDashboardMetrics::class)->forPeriod('all_time')['attention']['pending_workers']);
    }

    public function test_action_queue_orders_oldest_items_and_reconciles_counts(): void
    {
        $this->booking(Booking::STATUS_NEW, ['created_at' => now()->subDays(2)]);

        $stuck = $this->booking(Booking::STATUS_ACCEPTED, ['created_at' => now()->subDays(3)]);
        $stuck->forceFill(['created_at' => now()->subDays(3)])->saveQuietly();

        $dispute = Dispute::create([
            'booking_id' => $this->booking(Booking::STATUS_COMPLETED)->id,
            'raised_by' => $this->client->id,
            'reported_worker_id' => $this->worker->id,
            'type' => 'booking_dispute',
            'reason' => 'Old dispute',
            'status' => 'open',
        ]);
        $dispute->forceFill(['created_at' => now()->subDays(4)])->saveQuietly();

        $document = WorkerDocument::create([
            'user_id' => $this->worker->id,
            'document_type' => 'Government-Issued ID',
            'file_path' => 'documents/id.jpg',
            'status' => 'pending',
        ]);
        $document->forceFill(['created_at' => now()->subDays(5)])->saveQuietly();

        $metrics = app(AdminDashboardMetrics::class)->forPeriod('all_time', 'age');
        $queue = $metrics['action_queue'];

        $this->assertSame(4, $metrics['attention']['total_items']);
        $this->assertSame(4, $queue['total']);
        $this->assertSame(4, $queue['shown']);
        $this->assertSame('age', $queue['sort']);
        $this->assertSame(
            ['verification', 'dispute', 'stuck', 'unassigned'],
            array_column($queue['items'], 'type')
        );
        $this->assertSame(5 * 24 * 60, $queue['items'][0]['age_minutes']);
        $this->assertSame($queue['oldest_minutes'], $queue['items'][0]['age_minutes']);

        $priority = app(AdminDashboardMetrics::class)->forPeriod('all_time', 'priority')['action_queue'];
        $this->assertSame(
            ['dispute', 'stuck', 'unassigned', 'verification'],
            array_column($priority['items'], 'type')
        );
    }

    public function test_action_queue_export_streams_visible_rows(): void
    {
        $booking = $this->booking(Booking::STATUS_NEW, ['created_at' => now()->subDays(2)]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.dashboard.export', ['period' => '30d']))
            ->assertOk();

        $this->assertStringContainsString('text/csv', (string) $response->headers->get('content-type'));

        $csv = $response->streamedContent();
        $this->assertStringContainsString('Priority', $csv);
        $this->assertStringContainsString('Unassigned', $csv);
        $this->assertStringContainsString($booking->booking_ref, $csv);
    }

    public function test_completion_drilldown_uses_outcome_dates_not_creation_dates(): void
    {
        $completedInPeriod = $this->booking(Booking::STATUS_COMPLETED, [
            'price' => 500,
            'created_at' => now()->subMonths(2),
            'completed_at' => now()->subDays(2),
        ]);

        $this->booking(Booking::STATUS_COMPLETED, [
            'price' => 900,
            'created_at' => now()->subDays(2),
            'completed_at' => now()->subMonths(2),
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.bookings.index', [
                'status' => 'completed',
                'date_basis' => 'outcome',
                'date_from' => now()->subDays(6)->format('Y-m-d'),
                'date_to' => now()->format('Y-m-d'),
            ]))
            ->assertOk();

        $this->assertSame(1, $response->viewData('bookings')->total());
        $this->assertSame($completedInPeriod->id, $response->viewData('bookings')->first()->id);
        $this->assertSame(
            1,
            app(AdminDashboardMetrics::class)->forPeriod('7d')['kpis']['completion_rate']['completed']
        );
    }

    private function booking(string $status, array $attributes = []): Booking
    {
        $booking = Booking::create(array_merge([
            'client_id' => $this->client->id,
            'worker_id' => $this->worker->id,
            'service_category' => 'Electrical',
            'scheduled_at' => now()->addDay(),
            'address' => 'Poblacion, Tuy, Batangas',
            'status' => $status,
            'price' => 100,
            'tip_amount' => 0,
            'completed_at' => $status === Booking::STATUS_COMPLETED ? now() : null,
            'cancelled_at' => $status === Booking::STATUS_CANCELLED ? now() : null,
            'declined_at' => $status === Booking::STATUS_DECLINED ? now() : null,
        ], $attributes));

        if (isset($attributes['created_at'])) {
            $booking->forceFill(['created_at' => $attributes['created_at']])->saveQuietly();
        }

        return $booking;
    }
}
