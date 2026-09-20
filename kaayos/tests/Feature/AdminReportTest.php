<?php

namespace Tests\Feature;

use App\Models\Booking;
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
            'gross_amount' => 1000.00,
            'platform_fee' => 100.00,
            'net_amount' => 900.00,
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
