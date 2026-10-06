<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BookingMaterial;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BookingMaterialsTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    protected User $client;
    protected User $worker;
    protected User $admin;
    protected User $otherClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = User::factory()->create(['role' => 'client']);
        $this->worker = User::factory()->create(['role' => 'worker']);
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->otherClient = User::factory()->create(['role' => 'client']);
    }

    protected function createBooking(string $status = Booking::STATUS_ACCEPTED, float $price = 500.00): Booking
    {
        return Booking::create([
            'client_id'        => $this->client->id,
            'worker_id'        => $this->worker->id,
            'service_category' => 'Plumbing',
            'scheduled_at'     => now()->addDay(),
            'address'          => '123, Brgy. Bayanan, Tuy, Batangas',
            'house_no'         => '123',
            'barangay'         => 'Brgy. Bayanan',
            'status'           => $status,
            'price'            => $price,
            'completed_at'     => $status === Booking::STATUS_COMPLETED ? now() : null,
        ]);
    }

    protected function fakeJpeg(): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'kaayosjpg');
        $jpeg = base64_decode(
            '/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRof' .
            'Hh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwh' .
            'MjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjL/wAAR' .
            'CAABAAEDASIAAhEBAxEB/8QAHwAAAQUBAQEBAQEAAAAAAAAAAAECAwQFBgcICQoL/8QAtRAAA' .
            'gEDAwIEAwUFBAQAAAF9AQIDAAQRBRIhMUEGE1FhByJxFDKBkaEII0KxwRVS0fAkM2JyggkKFh' .
            'cYGRolJicoKSo0NTY3ODk6Q0RFRkdISUpTVFVWV1hZWmNkZWZnaGlqc3R1dnd4eXqDhIWGh4i' .
            'JipKTlJWWl5iZmqKjpKWmp6ipqrKztLW2t7i5usLDxMXGx8jJytLT1NXW19jZ2uHi4+Tl5ufo' .
            '6erx8vP09fb3+Pn6/8QAHwEAAwEBAQEBAQEBAQAAAAAAAAECAwQFBgcICQoL/8QAtREAAgEC' .
            'BAQDBAcFBAQAAQJ3AAECAxEEBSExBhJBUQdhcRMiMoEIFEKRobHBCSMzUvAVYnLRChYkNOEl8R' .
            'cYGRomJygpKjU2Nzg5OkNERUZHSElKU1RVVldYWVpjZGVmZ2hpanN0dXZ3eHl6goOEhYaHiIm' .
            'KkpOUlZaXmJmaoqOkpaanqKmqsrO0tba3uLm6wsPExcbHyMnK0tPU1dbX2Nna4uPk5ebn6Onq' .
            '8vP09fb3+Pn6/9oADAMBAAIRAxEAPwD3+iiigD//2Q=='
        );
        file_put_contents($path, $jpeg);

        return new UploadedFile($path, 'receipt.jpg', 'image/jpeg', null, true);
    }

    public function test_worker_can_add_material_and_totals_recompute(): void
    {
        $booking = $this->createBooking();

        $response = $this->actingAs($this->worker)
            ->postJson("/worker/bookings/{$booking->id}/materials", [
                'name'       => 'PVC pipe 1/2"',
                'qty'        => 2,
                'unit_price' => 50,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('materials_total', 100.0)
            ->assertJsonPath('invoice_total', 600.0);

        $this->assertDatabaseHas('booking_materials', [
            'booking_id' => $booking->id,
            'name'       => 'PVC pipe 1/2"',
            'line_total' => 100.00,
        ]);

        $fresh = $booking->fresh();
        $this->assertEquals(100.00, (float) $fresh->materials_total);
        $this->assertEquals(600.00, $fresh->invoice_total);
    }

    public function test_line_total_is_computed_server_side(): void
    {
        $booking = $this->createBooking();

        $this->actingAs($this->worker)
            ->postJson("/worker/bookings/{$booking->id}/materials", [
                'name'       => 'Copper wire',
                'qty'        => 3,
                'unit_price' => 25,
                'line_total' => 999,
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('booking_materials', [
            'booking_id' => $booking->id,
            'line_total' => 75.00,
        ]);
        $this->assertEquals(75.00, (float) $booking->fresh()->materials_total);
    }

    public function test_worker_can_update_material_and_totals_recompute(): void
    {
        $booking = $this->createBooking();
        $material = $booking->materials()->create([
            'name'       => 'Cement',
            'qty'        => 1,
            'unit_price' => 100,
            'line_total' => 100,
        ]);

        $this->actingAs($this->worker)
            ->patchJson("/worker/bookings/{$booking->id}/materials/{$material->id}", [
                'name'       => 'Cement',
                'qty'        => 4,
                'unit_price' => 120,
            ])
            ->assertOk()
            ->assertJsonPath('materials_total', 480.0)
            ->assertJsonPath('invoice_total', 980.0);

        $this->assertEquals(480.00, (float) $material->fresh()->line_total);
        $this->assertEquals(480.00, (float) $booking->fresh()->materials_total);
    }

    public function test_worker_can_delete_material_and_totals_recompute(): void
    {
        $booking = $this->createBooking();
        $material = $booking->materials()->create([
            'name'       => 'Cement',
            'qty'        => 2,
            'unit_price' => 100,
            'line_total' => 200,
        ]);
        $booking->update(['materials_total' => 200]);

        $this->actingAs($this->worker)
            ->deleteJson("/worker/bookings/{$booking->id}/materials/{$material->id}")
            ->assertOk()
            ->assertJsonPath('materials_total', 0.0)
            ->assertJsonPath('invoice_total', 500.0);

        $this->assertDatabaseMissing('booking_materials', ['id' => $material->id]);
        $this->assertEquals(0.00, (float) $booking->fresh()->materials_total);
    }

    public function test_worker_cannot_manage_materials_of_others_booking(): void
    {
        $booking = $this->createBooking();
        $otherWorker = User::factory()->create(['role' => 'worker']);

        $this->actingAs($otherWorker)
            ->postJson("/worker/bookings/{$booking->id}/materials", [
                'name'       => 'Screws',
                'qty'        => 1,
                'unit_price' => 10,
            ])
            ->assertForbidden();

        $this->assertEquals(0, $booking->materials()->count());
    }

    public function test_worker_cannot_add_materials_to_new_booking(): void
    {
        $booking = $this->createBooking(Booking::STATUS_NEW);

        $this->actingAs($this->worker)
            ->postJson("/worker/bookings/{$booking->id}/materials", [
                'name'       => 'Screws',
                'qty'        => 1,
                'unit_price' => 10,
            ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Materials can no longer be edited for this booking.');
    }

    public function test_worker_cannot_edit_materials_on_completed_booking(): void
    {
        $booking = $this->createBooking(Booking::STATUS_COMPLETED);

        $this->actingAs($this->worker)
            ->postJson("/worker/bookings/{$booking->id}/materials", [
                'name'       => 'Late addition',
                'qty'        => 1,
                'unit_price' => 10,
            ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Materials can no longer be edited for this booking.');
    }

    public function test_admin_can_add_material_to_completed_booking_and_earning_resyncs(): void
    {
        $booking = $this->createBooking(Booking::STATUS_COMPLETED, 1000.00);
        $booking->syncEarning();

        $this->assertDatabaseHas('earnings', [
            'booking_id'   => $booking->id,
            'gross_amount' => 1000.00,
            'platform_fee' => 100.00,
            'net_amount'   => 900.00,
        ]);

        $this->actingAs($this->admin)
            ->postJson("/admin/bookings/{$booking->id}/materials", [
                'name'       => 'Replacement valve',
                'qty'        => 1,
                'unit_price' => 250,
            ])
            ->assertStatus(201)
            ->assertJsonPath('materials_total', 250.0)
            ->assertJsonPath('invoice_total', 1250.0);

        // Materials are passed through to the worker; fee stays on the service price.
        $this->assertDatabaseHas('earnings', [
            'booking_id'   => $booking->id,
            'gross_amount' => 1250.00,
            'platform_fee' => 100.00,
            'net_amount'   => 1150.00,
        ]);
    }

    public function test_admin_can_delete_material_on_any_status(): void
    {
        $booking = $this->createBooking(Booking::STATUS_COMPLETED, 800.00);
        $material = $booking->materials()->create([
            'name'       => 'Wrong item',
            'qty'        => 1,
            'unit_price' => 200,
            'line_total' => 200,
        ]);
        $booking->update(['materials_total' => 200]);
        $booking->syncEarning();

        $this->actingAs($this->admin)
            ->deleteJson("/admin/bookings/{$booking->id}/materials/{$material->id}")
            ->assertOk()
            ->assertJsonPath('materials_total', 0.0);

        $this->assertDatabaseMissing('booking_materials', ['id' => $material->id]);
        $this->assertDatabaseHas('earnings', [
            'booking_id'   => $booking->id,
            'gross_amount' => 800.00,
            'platform_fee' => 80.00,
            'net_amount'   => 720.00,
        ]);
    }

    public function test_worker_route_rejects_client_role(): void
    {
        $booking = $this->createBooking();

        $this->actingAs($this->client)
            ->postJson("/worker/bookings/{$booking->id}/materials", [
                'name'       => 'Screws',
                'qty'        => 1,
                'unit_price' => 10,
            ])
            ->assertForbidden();
    }

    public function test_guest_cannot_manage_materials(): void
    {
        $booking = $this->createBooking();

        $this->post("/worker/bookings/{$booking->id}/materials", [
            'name'       => 'Screws',
            'qty'        => 1,
            'unit_price' => 10,
        ])->assertRedirect('/login');
    }

    public function test_material_validation_requires_name_qty_and_unit_price(): void
    {
        $booking = $this->createBooking();

        $this->actingAs($this->worker)
            ->postJson("/worker/bookings/{$booking->id}/materials", [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'qty', 'unit_price']);

        $this->assertEquals(0, $booking->materials()->count());
    }

    public function test_receipt_photo_is_stored_on_public_disk(): void
    {
        Storage::fake('public');
        $booking = $this->createBooking();

        $response = $this->actingAs($this->worker)
            ->postJson("/worker/bookings/{$booking->id}/materials", [
                'name'     => 'Paint',
                'qty'      => 1,
                'unit_price' => 300,
                'receipt'  => $this->fakeJpeg(),
            ]);

        $response->assertStatus(201);

        $material = $booking->materials()->first();
        $this->assertNotNull($material);
        $this->assertNotNull($material->receipt_photo_path);
        Storage::disk('public')->assertExists($material->receipt_photo_path);
        $this->assertNotNull($material->receipt_url);
    }

    public function test_completion_flow_includes_materials_in_earning(): void
    {
        \Illuminate\Support\Facades\Notification::fake();

        $booking = $this->createBooking(Booking::STATUS_ACCEPTED, 500.00);

        $this->actingAs($this->worker)
            ->postJson("/worker/bookings/{$booking->id}/materials", [
                'name'       => 'Pipe fittings',
                'qty'        => 1,
                'unit_price' => 100,
            ])
            ->assertStatus(201);

        $this->actingAs($this->worker)
            ->patchJson("/worker/jobs/{$booking->id}/status", ['status' => Booking::STATUS_IN_PROGRESS])
            ->assertOk();

        $this->actingAs($this->worker)
            ->patchJson("/worker/jobs/{$booking->id}/status", ['status' => Booking::STATUS_COMPLETED])
            ->assertOk();

        $this->actingAs($this->client)
            ->postJson("/client/bookings/{$booking->id}/confirm-complete")
            ->assertOk();

        $this->assertDatabaseHas('earnings', [
            'booking_id'   => $booking->id,
            'worker_id'    => $this->worker->id,
            'gross_amount' => 600.00,
            'platform_fee' => 50.00,
            'net_amount'   => 550.00,
        ]);
    }

    public function test_invoice_is_accessible_to_participants_and_admin_only(): void
    {
        $booking = $this->createBooking(Booking::STATUS_COMPLETED, 500.00);
        $booking->materials()->create([
            'name'       => 'Cement',
            'qty'        => 1,
            'unit_price' => 100,
            'line_total' => 100,
        ]);
        $booking->update(['materials_total' => 100]);

        $this->actingAs($this->client)
            ->get("/bookings/{$booking->id}/invoice")
            ->assertOk()
            ->assertSee('INVOICE')
            ->assertSee('Total due')
            ->assertSee(number_format(600.00, 2));

        $this->actingAs($this->worker)
            ->get("/bookings/{$booking->id}/invoice")
            ->assertOk();

        $this->actingAs($this->admin)
            ->get("/bookings/{$booking->id}/invoice")
            ->assertOk();

        $this->actingAs($this->otherClient)
            ->get("/bookings/{$booking->id}/invoice")
            ->assertForbidden();
    }

    public function test_client_booking_payload_includes_materials_and_invoice_url(): void
    {
        $booking = $this->createBooking(Booking::STATUS_COMPLETED, 500.00);
        $booking->materials()->create([
            'name'       => 'Cement',
            'qty'        => 1,
            'unit_price' => 100,
            'line_total' => 100,
        ]);
        $booking->update(['materials_total' => 100]);

        $response = $this->actingAs($this->client)->get('/client/bookings');
        $response->assertOk();
        $response->assertSee('materials_total', false);
        $response->assertSee('invoice_url', false);
        $response->assertSee('Cement', false);
    }
}
