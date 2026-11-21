<?php

namespace Tests\Feature\Admin;

use App\Models\Booking;
use App\Models\ProviderService;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Models\WorkerProfile;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPriceVisibilityTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_dashboard_shows_only_the_three_required_financial_metrics(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.dashboard'))
            ->assertOk();

        $response->assertSee(__('admin.dash.kpi.platform_fees'));
        $response->assertSee(__('admin.dash.kpi.total_transaction_value'));
        $response->assertSee(__('admin.dash.kpi.platform_revenue'));
        $response->assertDontSee(__('admin.dash.kpi.revenue'));
        $response->assertDontSee(__('admin.dash.kpi.gross_collected'));
    }

    public function test_worker_list_hides_hourly_daily_and_task_rates(): void
    {
        $worker = User::factory()->create(['role' => 'worker']);

        WorkerProfile::create([
            'user_id' => $worker->id,
            'hourly_rate' => 500,
            'daily_rate' => 4000,
            'task_base_rate' => 250,
            'preferred_payment_scheme' => 'task',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.workers.index'))
            ->assertOk();

        $response->assertSee('task');
        $response->assertDontSee('500.00');
        $response->assertDontSee('4,000.00');
        $response->assertDontSee('250.00');
    }

    public function test_provider_services_page_hides_custom_price(): void
    {
        $category = ServiceCategory::create([
            'name' => 'Plumbing',
            'slug' => 'plumbing',
            'is_active' => true,
        ]);

        $service = Service::create([
            'category_id' => $category->id,
            'name' => 'Pipe Repair',
            'slug' => 'pipe-repair',
            'base_price' => 100,
            'billing_type' => 'fixed',
            'is_active' => true,
        ]);

        $worker = User::factory()->create(['role' => 'worker']);

        ProviderService::create([
            'user_id' => $worker->id,
            'service_id' => $service->id,
            'custom_price' => 999.99,
            'is_available' => true,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.provider-services.index'))
            ->assertOk();

        $response->assertSee('Pipe Repair');
        $response->assertDontSee('999.99');
    }

    public function test_user_detail_hides_booking_price(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $worker = User::factory()->create(['role' => 'worker']);

        Booking::create([
            'client_id' => $client->id,
            'worker_id' => $worker->id,
            'service_category' => 'Plumbing',
            'scheduled_at' => now()->addDay(),
            'address' => 'Poblacion, Tuy, Batangas',
            'status' => Booking::STATUS_COMPLETED,
            'price' => 1234.56,
            'completed_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.show', $worker))
            ->assertOk();

        $response->assertDontSee('1,234.56');
    }

    public function test_worker_performance_report_has_no_gross_revenue_column(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $worker = User::factory()->create(['role' => 'worker']);

        Booking::create([
            'client_id' => $client->id,
            'worker_id' => $worker->id,
            'service_category' => 'Plumbing',
            'scheduled_at' => now()->addDay(),
            'address' => 'Poblacion, Tuy, Batangas',
            'status' => Booking::STATUS_COMPLETED,
            'price' => 1000,
            'completed_at' => now(),
        ]);

        $result = app(ReportService::class)->build(
            'worker_performance',
            '2000-01-01',
            now()->format('Y-m-d')
        );

        $this->assertArrayNotHasKey('Gross Revenue', $result['column_meta']);
        $this->assertNotContains('Gross Revenue', $result['columns']);
        $this->assertArrayNotHasKey('Gross Revenue', $result['totals']);
        $this->assertArrayNotHasKey('Gross Revenue', collect($result['summary'])->keyBy('label')->all());

        foreach ($result['rows'] as $row) {
            $this->assertArrayNotHasKey('Gross Revenue', $row);
        }
    }

    public function test_worker_performance_csv_export_has_no_gross_revenue_column(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.reports.export', [
                'type' => 'worker_performance',
                'date_from' => '2000-01-01',
                'date_to' => now()->format('Y-m-d'),
                'format' => 'csv',
            ]))
            ->assertOk();

        $csv = $response->streamedContent();

        $this->assertStringNotContainsString('Gross Revenue', $csv);
    }
}
