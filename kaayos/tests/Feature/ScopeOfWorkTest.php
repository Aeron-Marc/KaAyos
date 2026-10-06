<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\IssueCategory;
use App\Models\User;
use App\Models\WorkerProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ScopeOfWorkTest extends TestCase
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
            'user_id'      => $this->worker->id,
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

    protected function validScopeData(array $overrides = []): array
    {
        return array_merge([
            'worker_id'         => $this->worker->id,
            'service_category'  => 'Plumbing',
            'issue_category_id' => IssueCategory::first()->id,
            'urgency'           => 'normal',
            'scheduled_at'      => now()->addDays(2)->setTime(10, 0)->format('Y-m-d H:i:s'),
            'house_no'          => '123',
            'barangay'          => 'Luna',
            'notes'             => 'The kitchen sink pipe is leaking heavily under the cabinet.',
            'price'             => 500,
        ], $overrides);
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

        return new UploadedFile($path, 'issue.jpg', 'image/jpeg', null, true);
    }

    public function test_booking_requires_issue_category(): void
    {
        $response = $this->actingAs($this->client)
            ->postJson(route('client.bookings.store'), $this->validScopeData(['issue_category_id' => null]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['issue_category_id']);
    }

    public function test_booking_rejects_invalid_urgency(): void
    {
        $response = $this->actingAs($this->client)
            ->postJson(route('client.bookings.store'), $this->validScopeData(['urgency' => 'asap']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['urgency']);
    }

    public function test_booking_requires_problem_description_of_at_least_20_chars(): void
    {
        $response = $this->actingAs($this->client)
            ->postJson(route('client.bookings.store'), $this->validScopeData(['notes' => 'Broken pipe']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['notes']);
    }

    public function test_booking_rejects_inactive_issue_category(): void
    {
        $inactive = IssueCategory::create([
            'name'      => 'Retired Issue',
            'slug'      => 'retired-issue',
            'is_active' => false,
        ]);

        $response = $this->actingAs($this->client)
            ->postJson(route('client.bookings.store'), $this->validScopeData(['issue_category_id' => $inactive->id]));

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_emergency_booking_requires_at_least_one_photo(): void
    {
        $response = $this->actingAs($this->client)->post(
            route('client.bookings.store'),
            $this->validScopeData(['urgency' => 'emergency']),
            ['Accept' => 'application/json']
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['photos']);
    }

    public function test_emergency_booking_stores_uploaded_client_photo(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $response = $this->actingAs($this->client)->post(
            route('client.bookings.store'),
            $this->validScopeData(['urgency' => 'emergency', 'photos' => [$this->fakeJpeg()]]),
            ['Accept' => 'application/json']
        );

        $response->assertOk()
            ->assertJsonPath('success', true);

        $booking = Booking::where('worker_id', $this->worker->id)->latest('id')->first();
        $this->assertNotNull($booking);
        $this->assertEquals('emergency', $booking->urgency);
        $this->assertEquals(1.25, (float) $booking->urgency_multiplier);
        $this->assertDatabaseHas('booking_photos', [
            'booking_id'  => $booking->id,
            'uploaded_by' => 'client',
        ]);
    }

    public function test_normal_booking_does_not_require_photos(): void
    {
        $response = $this->actingAs($this->client)->post(
            route('client.bookings.store'),
            $this->validScopeData(),
            ['Accept' => 'application/json']
        );

        $response->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_soon_urgency_applies_110_multiplier_to_price(): void
    {
        $response = $this->actingAs($this->client)->post(
            route('client.bookings.store'),
            $this->validScopeData(['urgency' => 'soon', 'price' => 500]),
            ['Accept' => 'application/json']
        );

        $response->assertOk()
            ->assertJsonPath('success', true);

        $booking = Booking::where('worker_id', $this->worker->id)->latest('id')->first();
        $this->assertEquals(1.10, (float) $booking->urgency_multiplier);
        $this->assertEquals(550.0, (float) $booking->price);
    }

    public function test_normal_urgency_keeps_base_price(): void
    {
        $response = $this->actingAs($this->client)->post(
            route('client.bookings.store'),
            $this->validScopeData(['urgency' => 'normal', 'price' => 500]),
            ['Accept' => 'application/json']
        );

        $response->assertOk();

        $booking = Booking::where('worker_id', $this->worker->id)->latest('id')->first();
        $this->assertEquals(1.00, (float) $booking->urgency_multiplier);
        $this->assertEquals(500.0, (float) $booking->price);
    }

    public function test_issue_categories_are_seeded_and_active(): void
    {
        $this->assertGreaterThanOrEqual(10, IssueCategory::count());
        $this->assertTrue(IssueCategory::where('slug', 'general-maintenance')->exists());
    }

    public function test_profile_page_groups_issue_types_by_worker_trade(): void
    {
        $this->worker->update(['service_category' => 'plumbing']);

        $response = $this->actingAs($this->client)->get(route('workers.show', $this->worker));

        $response->assertOk();
        // single trade group: plumbing issues + "Other / Not Listed", no general group
        $response->assertSee('For Plumbing');
        $response->assertDontSee('General issues');
        $response->assertSee('fa-droplet');
        $response->assertSee('fa-ellipsis'); // Other / Not Listed chip always present
        $response->assertDontSee('fa-helmet-safety'); // General Maintenance hidden for trade workers
        $response->assertDontSee('fa-paint-roller'); // painting issue (other trade)
    }

    public function test_profile_page_shows_all_issue_types_for_worker_without_trade(): void
    {
        $this->worker->update(['service_category' => null]);

        $response = $this->actingAs($this->client)->get(route('workers.show', $this->worker));

        $response->assertOk();
        $response->assertDontSee('General issues'); // flat list = no group headers
        $response->assertSee('fa-droplet');
        $response->assertSee('fa-helmet-safety');
        $response->assertSee('fa-paint-roller');
    }

    public function test_booking_rejects_issue_type_from_another_trade(): void
    {
        $this->worker->update(['service_category' => 'plumbing']);
        $paintingIssue = IssueCategory::where('slug', 'painting')->firstOrFail();

        $response = $this->actingAs($this->client)
            ->postJson(route('client.bookings.store'), $this->validScopeData([
                'issue_category_id' => $paintingIssue->id,
            ]));

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors(['issue_category_id']);
    }

    public function test_booking_accepts_general_issue_for_any_trade(): void
    {
        $this->worker->update(['service_category' => 'welding']);
        $generalIssue = IssueCategory::where('slug', 'general-maintenance')->firstOrFail();

        $response = $this->actingAs($this->client)
            ->postJson(route('client.bookings.store'), $this->validScopeData([
                'issue_category_id' => $generalIssue->id,
            ]));

        $response->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_admin_can_create_issue_category_with_trade(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('admin.issue-categories.store'), [
            'name'             => 'Water Heater Repair',
            'slug'             => 'water-heater-repair',
            'icon'             => 'fa-hot-tub-person',
            'service_category' => 'plumbing',
        ]);

        $response->assertRedirect(route('admin.issue-categories.index'));
        $this->assertDatabaseHas('issue_categories', [
            'slug'             => 'water-heater-repair',
            'service_category' => 'plumbing',
        ]);
    }

    public function test_admin_issue_category_rejects_unknown_trade(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('admin.issue-categories.store'), [
            'name'             => 'Bad Trade Issue',
            'slug'             => 'bad-trade-issue',
            'service_category' => 'not-a-trade',
        ]);

        $response->assertSessionHasErrors('service_category');
    }
}
