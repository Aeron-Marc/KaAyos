<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BookingQuote;
use App\Models\Dispute;
use App\Models\Earning;
use App\Models\Review;
use App\Models\User;
use App\Models\WorkerDocument;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use ZipArchive;

class AdminReportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_guest_is_redirected_from_reports_page(): void
    {
        $this->get(route('admin.reports.index'))->assertRedirect();
    }

    public function test_non_admin_cannot_access_reports_page(): void
    {
        $client = User::factory()->create(['role' => 'client']);

        $this->actingAs($client)
            ->get(route('admin.reports.index'))
            ->assertForbidden();
    }

    public function test_admin_can_view_reports_page(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.reports.index'))
            ->assertOk()
            ->assertViewIs('admin.reports.index')
            ->assertViewHas('groups')
            ->assertViewHas('presets')
            ->assertSee('All Time', false);
    }

    public function test_admin_can_view_preview_for_a_report_type(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.reports.index', ['type' => 'bookings', 'preset' => '30d']))
            ->assertOk();

        $response->assertViewHas('type', 'bookings');
        $this->assertNotNull($response->viewData('preview'));
    }

    public function test_report_service_builds_every_report_type(): void
    {
        $this->seedReportData();

        $service = app(ReportService::class);
        $from = '2000-01-01';
        $to = now()->format('Y-m-d');

        foreach (ReportService::REPORT_KEYS as $type) {
            $result = $service->build($type, $from, $to);

            $this->assertArrayHasKey('summary', $result, $type);
            $this->assertArrayHasKey('chart', $result, $type);
            $this->assertArrayHasKey('columns', $result, $type);
            $this->assertArrayHasKey('rows', $result, $type);
            $this->assertArrayHasKey('total_rows', $result, $type);
            $this->assertNotEmpty($result['columns'], $type);
            $this->assertIsArray($result['rows'], $type);
        }
    }

    public function test_bookings_report_reflects_seeded_data(): void
    {
        $this->seedReportData();

        $result = app(ReportService::class)->build('bookings', '2000-01-01', now()->format('Y-m-d'));

        $this->assertSame(3, $result['total_rows']);
        $this->assertSame(3, $result['summary'][0]['value']);
        $this->assertCount(3, $result['rows']);
    }

    public function test_bookings_report_uses_terminal_outcomes_and_completed_value(): void
    {
        $this->seedReportData();

        $result = app(ReportService::class)->build('bookings', '2000-01-01', now()->format('Y-m-d'));
        $summary = collect($result['summary'])->keyBy('label');

        $this->assertSame(3, $summary['Bookings Created']['value']);
        $this->assertSame(1, $summary['Completed']['value']);
        $this->assertSame(1, $summary['Cancelled']['value']);
        $this->assertSame(0, $summary['Declined']['value']);
        $this->assertSame('50%', $summary['Completion Rate']['value']);
        $this->assertEquals(1000.0, $summary['Gross Value']['value']);
    }

    public function test_revenue_report_uses_completed_booking_price_instead_of_quote_amount(): void
    {
        $this->seedReportData();
        $completed = Booking::where('status', Booking::STATUS_COMPLETED)->firstOrFail();

        BookingQuote::updateOrCreate(
            ['booking_id' => $completed->id],
            [
                'worker_id' => $completed->worker_id,
                'total_amount' => 9999.00,
                'tip_amount' => 50.00,
                'paid_at' => now(),
            ]
        );

        $result = app(ReportService::class)->build('revenue', '2000-01-01', now()->format('Y-m-d'));
        $summary = collect($result['summary'])->keyBy('label');

        $this->assertEquals(1000.0, $summary['Gross Value']['value']);
        $this->assertEquals(0.0, $summary['Total Tips']['value']);
        $this->assertEquals(1000.0, $summary['Gross Collected']['value']);
        $this->assertEquals(1000.0, $result['rows'][0]['Gross Value']);
        $this->assertSame('money', $result['column_meta']['Gross Value']['type']);
    }

    public function test_report_sort_is_applied_before_preview_limit_and_csv_export(): void
    {
        $this->seedReportData();

        $result = app(ReportService::class)->build('bookings', '2000-01-01', now()->format('Y-m-d'), 2, 5, 'desc');
        $this->assertCount(2, $result['rows']);
        $this->assertEquals(1000.0, $result['rows'][0]['Price']);
        $this->assertEquals(500.0, $result['rows'][1]['Price']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.reports.export', [
                'type' => 'bookings',
                'format' => 'csv',
                'date_from' => '2000-01-01',
                'date_to' => now()->format('Y-m-d'),
                'sort_col' => 5,
                'sort_dir' => 'desc',
            ]));

        $response->assertOk();
        $lines = preg_split('/\r\n|\n/', trim($response->streamedContent()));
        $firstRow = str_getcsv($lines[1]);

        $this->assertEquals(1000.0, (float) $firstRow[5]);
    }

    public function test_reports_reject_reversed_dates(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.reports.index', [
                'type' => 'bookings',
                'date_from' => '2026-10-10',
                'date_to' => '2026-10-01',
            ]))
            ->assertRedirect(route('admin.reports.index', ['type' => 'bookings']))
            ->assertSessionHasErrors(['date_to']);
    }

    public function test_report_rejects_invalid_sort_columns(): void
    {
        $this->seedReportData();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.reports.index', [
                'type' => 'bookings',
                'preset' => 'all',
                'sort_col' => 999,
                'sort_dir' => 'desc',
            ]))
            ->assertOk();

        $this->assertNull($response->viewData('sortCol'));
    }

    public function test_csv_export_returns_valid_csv(): void
    {
        $this->seedReportData();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.reports.export', [
                'type' => 'bookings',
                'format' => 'csv',
                'date_from' => '2000-01-01',
                'date_to' => now()->format('Y-m-d'),
            ]));

        $response->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->assertHeaderContains('Content-Disposition', '.csv');

        $body = $response->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $body);
        $this->assertStringContainsString('Booking Ref', $body);
        $this->assertStringContainsString('Electrical', $body);
    }

    public function test_xlsx_export_returns_valid_workbook(): void
    {
        $this->seedReportData();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.reports.export', [
                'type' => 'bookings',
                'format' => 'xlsx',
                'date_from' => '2000-01-01',
                'date_to' => now()->format('Y-m-d'),
            ]));

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->assertHeaderContains('Content-Disposition', '.xlsx');

        $tmp = tempnam(sys_get_temp_dir(), 'report').'.xlsx';
        file_put_contents($tmp, $response->streamedContent());

        $zip = new ZipArchive;
        $this->assertTrue($zip->open($tmp) === true);
        $this->assertNotEmpty($zip->getFromName('xl/workbook.xml'));
        $this->assertNotEmpty($zip->getFromName('xl/media/image1.jpeg'));
        $this->assertNotEmpty($zip->getFromName('xl/media/image2.jpeg'));
        $this->assertNotEmpty($zip->getFromName('xl/drawings/drawing1.xml'));

        $sheet = $zip->getFromName('xl/worksheets/sheet1.xml');
        $this->assertStringContainsString('Republic of the Philippines', $sheet);
        $this->assertStringContainsString('PUBLIC EMPLOYMENT SERVICE OFFICE', $sheet);
        $this->assertStringContainsString('Bookings Report', $sheet);
        $this->assertStringContainsString('Booking Ref', $sheet);
        $this->assertStringContainsString('<drawing r:id="rId1"/>', $sheet);
        $zip->close();

        @unlink($tmp);
    }

    public function test_admin_can_print_full_report(): void
    {
        $this->seedReportData();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.reports.print', [
                'type' => 'bookings',
                'date_from' => '2000-01-01',
                'date_to' => now()->format('Y-m-d'),
            ]));

        $response->assertOk()
            ->assertViewIs('admin.reports.print')
            ->assertSee('Republic of the Philippines')
            ->assertSee('PUBLIC EMPLOYMENT SERVICE OFFICE')
            ->assertSee('Bookings Report')
            ->assertSee('Total Records: 3')
            ->assertSee('Plumbing', false);
    }

    public function test_print_requires_valid_formatless_params(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.reports.print', [
                'type' => 'bookings',
                'date_from' => '2000-01-01',
                'date_to' => '1999-01-01',
            ]))
            ->assertSessionHasErrors(['date_to']);
    }

    public function test_print_actions_are_hidden_when_date_validation_fails(): void
    {
        $this->seedReportData();

        $response = $this->actingAs($this->admin)
            ->followingRedirects()
            ->get(route('admin.reports.index', [
                'type' => 'bookings',
                'date_from' => '2026-10-10',
                'date_to' => '2026-10-01',
            ]));

        $response->assertOk()
            ->assertDontSee(__('admin.reports.print_now'))
            ->assertDontSee(__('admin.reports.print_preview'));
    }

    public function test_print_view_includes_chart_only_for_financial_and_performance_reports(): void
    {
        $this->seedReportData();

        $revenue = $this->actingAs($this->admin)->get(route('admin.reports.print', [
            'type' => 'revenue',
            'date_from' => '2000-01-01',
            'date_to' => now()->format('Y-m-d'),
        ]));

        $revenue->assertOk()->assertSee('printReportChart', false);

        $bookings = $this->actingAs($this->admin)->get(route('admin.reports.print', [
            'type' => 'bookings',
            'date_from' => '2000-01-01',
            'date_to' => now()->format('Y-m-d'),
        ]));

        $bookings->assertOk()->assertDontSee('printReportChart', false);
    }

    public function test_print_view_autoprints_when_requested(): void
    {
        $this->seedReportData();

        $auto = $this->actingAs($this->admin)->get(route('admin.reports.print', [
            'type' => 'bookings',
            'date_from' => '2000-01-01',
            'date_to' => now()->format('Y-m-d'),
            'autoprint' => 1,
        ]));

        $auto->assertOk()->assertSee('autoprint = true', false);

        $manual = $this->actingAs($this->admin)->get(route('admin.reports.print', [
            'type' => 'bookings',
            'date_from' => '2000-01-01',
            'date_to' => now()->format('Y-m-d'),
        ]));

        $manual->assertOk()->assertSee('autoprint = false', false);
    }

    public function test_print_back_link_preserves_preset(): void
    {
        $this->seedReportData();

        $response = $this->actingAs($this->admin)->get(route('admin.reports.print', [
            'type' => 'bookings',
            'date_from' => now()->subDays(29)->format('Y-m-d'),
            'date_to' => now()->format('Y-m-d'),
            'preset' => '30d',
        ]));

        $response->assertOk()->assertSee('preset=30d', false);
    }

    public function test_print_view_applies_sort_before_output(): void
    {
        $this->seedReportData();

        $response = $this->actingAs($this->admin)->get(route('admin.reports.print', [
            'type' => 'bookings',
            'date_from' => '2000-01-01',
            'date_to' => now()->format('Y-m-d'),
            'sort_col' => 5,
            'sort_dir' => 'desc',
        ]));

        $response->assertOk()
            ->assertSeeInOrder(['&#8369;500.00', '&#8369;0.00'], false);
    }

    private function seedReportData(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $worker = User::factory()->create(['role' => 'worker', 'service_category' => 'Plumbing']);

        $completed = Booking::create([
            'client_id' => $client->id,
            'worker_id' => $worker->id,
            'service_category' => 'Plumbing',
            'scheduled_at' => now(),
            'address' => '123 Test St',
            'house_no' => '123',
            'barangay' => 'Brgy. Test',
            'status' => Booking::STATUS_COMPLETED,
            'price' => 1000.00,
            'completed_at' => now(),
        ]);

        Earning::create([
            'worker_id' => $worker->id,
            'booking_id' => $completed->id,
            'total_amount' => 1000.00,
            'paid_at' => now(),
        ]);

        Booking::create([
            'client_id' => $client->id,
            'worker_id' => $worker->id,
            'service_category' => 'Plumbing',
            'scheduled_at' => now(),
            'address' => '456 Test Ave',
            'house_no' => '456',
            'barangay' => 'Brgy. Test',
            'status' => Booking::STATUS_CANCELLED,
            'price' => 500.00,
            'cancelled_at' => now(),
        ]);

        Booking::create([
            'client_id' => $client->id,
            'worker_id' => $worker->id,
            'service_category' => 'Electrical',
            'scheduled_at' => now()->addDay(),
            'address' => '789 Test Rd',
            'house_no' => '789',
            'barangay' => 'Brgy. Test',
            'status' => Booking::STATUS_NEW,
            'price' => 0.00,
        ]);

        Review::create([
            'booking_id' => $completed->id,
            'client_id' => $client->id,
            'worker_id' => $worker->id,
            'rating' => 5,
            'comment' => 'Excellent work',
        ]);

        Dispute::create([
            'type' => 'booking_dispute',
            'booking_id' => $completed->id,
            'raised_by' => $client->id,
            'reported_worker_id' => $worker->id,
            'status' => 'open',
            'reason' => 'Testing dispute',
        ]);

        WorkerDocument::create([
            'user_id' => $worker->id,
            'document_type' => 'valid_id',
            'file_path' => 'docs/worker-id.jpg',
            'status' => 'pending',
        ]);
    }
}
