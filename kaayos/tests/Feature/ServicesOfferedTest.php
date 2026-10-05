<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\ProviderService;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Models\WorkerProfile;
use App\Support\BillingTypeCatalog;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ServicesOfferedTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    protected User $client;
    protected User $worker;
    protected ServiceCategory $category;
    protected Service $leakRepair;
    protected Service $faucetReplacement;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        Event::fake();

        $this->client = User::factory()->create(['role' => 'client']);
        $this->worker = User::factory()->create(['role' => 'worker']);
        $this->createWorkerProfile($this->worker);

        $this->category = ServiceCategory::create([
            'name' => 'Plumbing', 'slug' => 'plumbing', 'is_active' => true,
        ]);

        $this->leakRepair = $this->makeService('Leak Repair', 'leak-repair', 350, 'hourly');
        $this->linkService($this->leakRepair);

        $this->faucetReplacement = $this->makeService('Faucet Replacement', 'faucet-replacement', 450, 'fixed');
        $this->linkService($this->faucetReplacement);
    }

    protected function createWorkerProfile(User $worker, ?float $hourlyRate = 350): void
    {
        WorkerProfile::create([
            'user_id'      => $worker->id,
            'hourly_rate'  => $hourlyRate,
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

    protected function makeService(string $name, string $slug, float $price, string $billing = 'either'): Service
    {
        return Service::create([
            'category_id' => $this->category->id,
            'name'        => $name,
            'slug'        => $slug,
            'base_price'  => $price,
            'billing_type' => $billing,
            'is_active'   => true,
        ]);
    }

    protected function linkService(Service $service, ?float $customPrice = null, bool $available = true): ProviderService
    {
        return ProviderService::create([
            'user_id'      => $this->worker->id,
            'service_id'   => $service->id,
            'custom_price' => $customPrice,
            'is_available' => $available,
        ]);
    }

    protected function validBookingData(array $overrides = []): array
    {
        return array_merge([
            'worker_id'        => $this->worker->id,
            'service_category' => 'Plumbing',
            'scheduled_at'     => now()->addDay()->format('Y-m-d H:i:s'),
            'house_no'         => '123',
            'barangay'         => 'Brgy. Bayanan',
            'pricing_type'     => 'fixed',
            'complexity_level' => 'standard',
        ], $overrides);
    }

    // ── Worker "My Services" management ────────────────────────

    public function test_worker_services_page_renders_linked_and_available_services(): void
    {
        $this->makeService('Pipe Installation', 'pipe-installation', 500);

        $this->actingAs($this->worker)
            ->get(route('worker.services.index'))
            ->assertOk()
            ->assertSee('My Services')
            ->assertSee('Leak Repair')
            ->assertSee('Available Services')
            ->assertSee('Pipe Installation');
    }

    public function test_worker_can_add_service(): void
    {
        $service = $this->makeService('Drain Cleaning', 'drain-cleaning', 300);

        $this->actingAs($this->worker)
            ->post(route('worker.services.add'), ['service_id' => $service->id])
            ->assertRedirect(route('worker.services.index'));

        $this->assertDatabaseHas('provider_services', [
            'user_id'    => $this->worker->id,
            'service_id' => $service->id,
            'is_available' => 1,
        ]);
    }

    public function test_adding_same_service_twice_does_not_duplicate(): void
    {
        $this->actingAs($this->worker)
            ->post(route('worker.services.add'), ['service_id' => $this->leakRepair->id]);

        $this->assertSame(1, ProviderService::where('user_id', $this->worker->id)
            ->where('service_id', $this->leakRepair->id)->count());
    }

    public function test_worker_cannot_set_price_for_service_not_linked_to_them(): void
    {
        $otherWorker = User::factory()->create(['role' => 'worker']);

        $this->actingAs($otherWorker)
            ->from(route('worker.services.index'))
            ->put(route('worker.services.price', $this->leakRepair), ['custom_price' => 999])
            ->assertNotFound();

        $this->assertDatabaseMissing('provider_services', [
            'user_id'      => $otherWorker->id,
            'service_id'   => $this->leakRepair->id,
            'custom_price' => 999,
        ]);
    }

    public function test_worker_can_set_and_clear_custom_price(): void
    {
        $this->actingAs($this->worker)
            ->put(route('worker.services.price', $this->leakRepair), ['custom_price' => 999])
            ->assertRedirect(route('worker.services.index'));

        $this->assertDatabaseHas('provider_services', [
            'service_id'   => $this->leakRepair->id,
            'custom_price' => 999,
        ]);

        $this->actingAs($this->worker)
            ->put(route('worker.services.price', $this->leakRepair), ['custom_price' => '']);

        $this->assertDatabaseHas('provider_services', [
            'service_id'   => $this->leakRepair->id,
            'custom_price' => null,
        ]);
    }

    public function test_worker_can_toggle_service_availability(): void
    {
        $this->actingAs($this->worker)
            ->patch(route('worker.services.toggle', $this->leakRepair))
            ->assertRedirect(route('worker.services.index'));

        $this->assertDatabaseHas('provider_services', [
            'service_id' => $this->leakRepair->id,
            'is_available' => 0,
        ]);
    }

    public function test_worker_can_remove_service(): void
    {
        $this->actingAs($this->worker)
            ->delete(route('worker.services.remove', $this->leakRepair))
            ->assertRedirect(route('worker.services.index'));

        $this->assertDatabaseMissing('provider_services', [
            'user_id'    => $this->worker->id,
            'service_id' => $this->leakRepair->id,
        ]);
    }

    // ── Client profile display ─────────────────────────────────

    public function test_worker_profile_page_shows_selectable_services(): void
    {
        $response = $this->actingAs($this->client)
            ->get(route('client.workers.show', $this->worker->id));

        $response->assertOk()
            ->assertSee('Services Offered')
            ->assertSee('service-select', false)
            ->assertSee('Leak Repair')
            ->assertSee('svc-tile-' . $this->leakRepair->id, false)
            ->assertSee('id="opt-fixed"', false)
            ->assertSee('id="opt-hourly"', false)
            ->assertSee('id="billing-lock-hint"', false)
            ->assertSee('id="rate-display"', false);
    }

    // ── Booking pricing with selected service ──────────────────

    public function test_booking_with_service_uses_service_base_price(): void
    {
        $response = $this->actingAs($this->client)->postJson(
            route('client.bookings.store'),
            $this->validBookingData(['service_id' => $this->faucetReplacement->id, 'price' => 123])
        );

        $response->assertOk()->assertJsonPath('success', true);

        $booking = Booking::latest('id')->first();
        $this->assertSame(450.0, (float) $booking->price);
        $this->assertSame($this->faucetReplacement->id, $booking->service_id);
        $this->assertSame('Faucet Replacement', $booking->service_name);
        $this->assertSame('Plumbing', $booking->service_category);
    }

    public function test_booking_with_custom_priced_service_uses_custom_price(): void
    {
        $this->faucetReplacement->providerServices()->update(['custom_price' => 999]);

        $this->actingAs($this->client)->postJson(
            route('client.bookings.store'),
            $this->validBookingData(['service_id' => $this->faucetReplacement->id])
        )->assertOk();

        $booking = Booking::latest('id')->first();
        $this->assertSame(999.0, (float) $booking->price);
    }

    public function test_booking_with_unavailable_service_is_rejected(): void
    {
        $this->leakRepair->providerServices()->update(['is_available' => false]);

        $this->actingAs($this->client)->postJson(
            route('client.bookings.store'),
            $this->validBookingData(['service_id' => $this->leakRepair->id])
        )->assertStatus(422);
    }

    public function test_booking_with_service_not_offered_by_worker_is_rejected(): void
    {
        $service = $this->makeService('Sewer Line Cleaning', 'sewer-line-cleaning', 600);

        $this->actingAs($this->client)->postJson(
            route('client.bookings.store'),
            $this->validBookingData(['service_id' => $service->id])
        )->assertStatus(422);
    }

    public function test_booking_hourly_mode_uses_hourly_rate_not_service_price(): void
    {
        $this->actingAs($this->client)->postJson(
            route('client.bookings.store'),
            $this->validBookingData([
                'service_id'              => $this->leakRepair->id,
                'pricing_type'            => 'hourly',
                'estimated_duration_hours' => 2,
            ])
        )->assertOk();

        $booking = Booking::latest('id')->first();
        $this->assertSame(700.0, (float) $booking->price); // 350/hr x 2h
        $this->assertSame($this->leakRepair->id, $booking->service_id);
    }

    public function test_booking_complexity_multiplier_applies_to_service_price(): void
    {
        $this->actingAs($this->client)->postJson(
            route('client.bookings.store'),
            $this->validBookingData([
                'service_id'       => $this->faucetReplacement->id,
                'complexity_level' => 'moderate',
            ])
        )->assertOk();

        $booking = Booking::latest('id')->first();
        $this->assertSame(540.0, (float) $booking->price); // 450 x 1.2
    }

    // ── Billing-type enforcement (anti-tamper) ─────────────────

    public function test_fixed_service_forces_fixed_pricing_server_side(): void
    {
        $this->actingAs($this->client)->postJson(
            route('client.bookings.store'),
            $this->validBookingData([
                'service_id'               => $this->faucetReplacement->id,
                'pricing_type'             => 'hourly',
                'estimated_duration_hours' => 0.5,
                'price'                    => 175,
            ])
        )->assertOk();

        $booking = Booking::latest('id')->first();
        $this->assertSame('fixed', $booking->pricing_type);
        $this->assertSame(450.0, (float) $booking->price); // base price, not 0.5h x rate
    }

    public function test_hourly_service_forces_hourly_pricing_server_side(): void
    {
        $this->actingAs($this->client)->postJson(
            route('client.bookings.store'),
            $this->validBookingData([
                'service_id'               => $this->leakRepair->id,
                'pricing_type'             => 'fixed',
                'estimated_duration_hours' => 2,
                'price'                    => 350,
            ])
        )->assertOk();

        $booking = Booking::latest('id')->first();
        $this->assertSame('hourly', $booking->pricing_type);
        $this->assertSame(700.0, (float) $booking->price); // 350/hr x 2h
    }

    // ── Billing type catalog ───────────────────────────────────

    public function test_billing_type_catalog_values_are_valid(): void
    {
        $valid = ['fixed', 'hourly', 'either'];

        foreach (BillingTypeCatalog::MAP as $name => $type) {
            $this->assertContains($type, $valid, "Invalid billing type for {$name}");
        }

        $this->assertSame('hourly', BillingTypeCatalog::MAP['Leak Repair']);
        $this->assertSame('fixed', BillingTypeCatalog::MAP['Faucet Replacement']);
        $this->assertSame('hourly', BillingTypeCatalog::MAP['Wiring & Rewiring']);
        $this->assertSame('fixed', BillingTypeCatalog::MAP['Manicure']);
        $this->assertGreaterThanOrEqual(90, count(BillingTypeCatalog::MAP));
    }

    // ── Profile category re-sync ───────────────────────────────

    public function test_profile_category_change_links_new_category_services(): void
    {
        $plumbingService = $this->leakRepair;
        $plumbingLink = $plumbingService->providerServices()->first();
        $plumbingLink->update(['custom_price' => 111]);

        $electricalCategory = ServiceCategory::create(['name' => 'Electrical', 'slug' => 'electrical', 'is_active' => true]);
        $wiring = Service::create([
            'category_id'  => $electricalCategory->id,
            'name'         => 'Wiring & Rewiring',
            'slug'         => 'wiring-rewiring',
            'base_price'   => 600,
            'billing_type' => 'hourly',
            'is_active'    => true,
        ]);

        $this->actingAs($this->worker)->put(route('worker.profile.update'), [
            'first_name'       => $this->worker->first_name,
            'last_name'        => $this->worker->last_name,
            'language'         => 'English',
            'service_category' => 'Electrical',
        ])->assertRedirect();

        // New category linked
        $this->assertDatabaseHas('provider_services', [
            'user_id'    => $this->worker->id,
            'service_id' => $wiring->id,
        ]);

        // Existing link and custom price untouched
        $this->assertDatabaseHas('provider_services', [
            'id'           => $plumbingLink->id,
            'custom_price' => 111,
        ]);
    }
}
