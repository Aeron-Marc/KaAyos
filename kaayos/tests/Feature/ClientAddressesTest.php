<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\ClientAddress;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Models\WorkerProfile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ClientAddressesTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    protected User $client;
    protected User $worker;
    protected Service $service;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        Event::fake();

        $this->client = User::factory()->create(['role' => 'client']);
        $this->worker = User::factory()->create(['role' => 'worker']);
        WorkerProfile::create([
            'user_id'      => $this->worker->id,
            'hourly_rate'  => 350,
            'availability' => [
                ['day' => 'Monday', 'active' => true, 'start' => '08:00', 'end' => '17:00'],
            ],
        ]);

        $category = ServiceCategory::create(['name' => 'Plumbing', 'slug' => 'plumbing', 'is_active' => true]);
        $this->service = Service::create([
            'category_id' => $category->id,
            'name'        => 'Leak Repair',
            'slug'        => 'leak-repair',
            'base_price'  => 350,
            'billing_type' => 'fixed',
            'is_active'   => true,
        ]);
        \App\Models\ProviderService::create([
            'user_id'      => $this->worker->id,
            'service_id'   => $this->service->id,
            'is_available' => true,
        ]);
    }

    protected function makeAddress(User $owner, array $overrides = []): ClientAddress
    {
        return ClientAddress::create(array_merge([
            'user_id'    => $owner->id,
            'label'      => 'Home',
            'house_no'   => '123 Mabini St',
            'barangay'   => 'Luna',
            'latitude'   => 14.0192,
            'longitude'  => 120.7353,
            'is_default' => false,
        ], $overrides));
    }

    protected function validBookingData(array $overrides = []): array
    {
        return array_merge([
            'worker_id'        => $this->worker->id,
            'service_category' => 'Plumbing',
            'scheduled_at'     => now()->addDay()->format('Y-m-d H:i:s'),
            'house_no'         => '123 Mabini St',
            'barangay'         => 'Luna',
            'pricing_type'     => 'fixed',
            'complexity_level' => 'standard',
        ], $overrides);
    }

    // ── CRUD + default handling ──────────────────────────────

    public function test_first_saved_address_becomes_default(): void
    {
        $this->actingAs($this->client)
            ->post(route('client.account.addresses.store'), [
                'label'     => 'Home',
                'house_no'  => '123 Mabini St',
                'barangay'  => 'Luna',
            ])
            ->assertRedirect(route('client.account.addresses'));

        $this->assertDatabaseHas('client_addresses', [
            'user_id'    => $this->client->id,
            'label'      => 'Home',
            'is_default' => 1,
        ]);
    }

    public function test_second_address_is_not_default_until_set_default(): void
    {
        $first = $this->makeAddress($this->client, ['is_default' => true, 'label' => 'Home']);
        $second = $this->makeAddress($this->client, ['label' => 'Office', 'house_no' => '45 Rizal Ave', 'barangay' => 'Dao']);

        $this->assertTrue((bool) $first->is_default);
        $this->assertFalse((bool) $second->is_default);

        $this->actingAs($this->client)
            ->patch(route('client.account.addresses.default', $second))
            ->assertRedirect(route('client.account.addresses'));

        $this->assertTrue((bool) $second->fresh()->is_default);
        $this->assertFalse((bool) $first->fresh()->is_default);
    }

    public function test_update_address_validates_barangay_against_tuy_list(): void
    {
        $address = $this->makeAddress($this->client);

        $this->actingAs($this->client)
            ->from(route('client.account.addresses'))
            ->put(route('client.account.addresses.update', $address), [
                'label'     => 'Home',
                'house_no'  => '123 Mabini St',
                'barangay'  => 'Definitely Not A Tuy Barangay',
            ])
            ->assertSessionHasErrors('barangay');

        $this->assertSame('Luna', $address->fresh()->barangay);
    }

    public function test_deleting_default_address_promotes_another(): void
    {
        $first = $this->makeAddress($this->client, ['is_default' => true, 'label' => 'Home']);
        $second = $this->makeAddress($this->client, ['label' => 'Office', 'barangay' => 'Dao']);

        $this->actingAs($this->client)
            ->delete(route('client.account.addresses.destroy', $first))
            ->assertRedirect(route('client.account.addresses'));

        $this->assertDatabaseMissing('client_addresses', ['id' => $first->id]);
        $this->assertTrue((bool) $second->fresh()->is_default);
    }

    public function test_client_cannot_modify_another_clients_address(): void
    {
        $otherClient = User::factory()->create(['role' => 'client']);
        $address = $this->makeAddress($otherClient);

        $payload = ['label' => 'X', 'house_no' => 'Y', 'barangay' => 'Luna'];

        $this->actingAs($this->client)
            ->put(route('client.account.addresses.update', $address), $payload)
            ->assertNotFound();

        $this->actingAs($this->client)
            ->delete(route('client.account.addresses.destroy', $address))
            ->assertNotFound();

        $this->actingAs($this->client)
            ->patch(route('client.account.addresses.default', $address))
            ->assertNotFound();

        $this->assertTrue(ClientAddress::whereKey($address->id)->exists());
    }

    // ── Pages ────────────────────────────────────────────────

    public function test_addresses_page_renders_saved_addresses(): void
    {
        $address = $this->makeAddress($this->client, ['label' => 'Home', 'is_default' => true]);

        $this->actingAs($this->client)
            ->get(route('client.account.addresses'))
            ->assertOk()
            ->assertSee('Saved Addresses')
            ->assertSee('Home')
            ->assertSee($address->fullAddress());
    }

    public function test_booking_page_renders_address_selector_with_default(): void
    {
        $this->makeAddress($this->client, ['label' => 'Home', 'is_default' => true]);
        $this->makeAddress($this->client, ['label' => 'Office', 'house_no' => '45 Rizal Ave', 'barangay' => 'Dao']);

        $response = $this->actingAs($this->client)
            ->get(route('client.workers.show', $this->worker->id));

        $response->assertOk()
            ->assertSee('id="address-select"', false)
            ->assertSee('id="saved-addr-summary"', false)
            ->assertSee('Home — 123 Mabini St, Brgy. Luna')
            ->assertSee('Other / new address')
            ->assertSee('id="manual-address-fields"', false);

        $html = $response->getContent();
        $homePos = strpos($html, 'Home — 123 Mabini St');
        $otherPos = strrpos($html, 'Other / new address');
        $this->assertLessThan($otherPos, $homePos, 'Default (Home) should be listed before "Other / new address"');
    }

    // ── Booking with saved address ───────────────────────────

    public function test_booking_with_address_id_snapshots_saved_address(): void
    {
        $address = $this->makeAddress($this->client, [
            'house_no'  => '99 Secret Lane',
            'barangay'  => 'Dao',
            'latitude'  => 13.9996,
            'longitude' => 120.7547,
        ]);

        $this->actingAs($this->client)->postJson(
            route('client.bookings.store'),
            $this->validBookingData([
                'address_id' => $address->id,
                'house_no'   => 'Tampered Street',
                'barangay'   => 'Rizal',
                'latitude'   => 1.5,
                'longitude'  => 2.5,
            ])
        )->assertOk()->assertJsonPath('success', true);

        $booking = Booking::latest('id')->first();
        $this->assertSame('99 Secret Lane', $booking->house_no);
        $this->assertSame('Dao', $booking->barangay);
        $this->assertEqualsWithDelta(13.9996, (float) $booking->latitude, 0.0001);
        $this->assertEqualsWithDelta(120.7547, (float) $booking->longitude, 0.0001);
        $this->assertStringContainsString('99 Secret Lane', $booking->address);
    }

    public function test_booking_with_address_id_does_not_require_manual_fields(): void
    {
        $address = $this->makeAddress($this->client);

        $payload = $this->validBookingData();
        unset($payload['house_no'], $payload['barangay']);
        $payload['address_id'] = $address->id;

        $this->actingAs($this->client)
            ->postJson(route('client.bookings.store'), $payload)
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSame('Luna', Booking::latest('id')->first()->barangay);
    }

    public function test_booking_with_foreign_address_id_is_rejected(): void
    {
        $otherClient = User::factory()->create(['role' => 'client']);
        $address = $this->makeAddress($otherClient);

        $payload = $this->validBookingData();
        unset($payload['house_no'], $payload['barangay']);
        $payload['address_id'] = $address->id;

        $this->actingAs($this->client)
            ->postJson(route('client.bookings.store'), $payload)
            ->assertStatus(422);

        $this->assertSame(0, Booking::count());
    }

    public function test_booking_without_address_id_still_requires_manual_fields(): void
    {
        $payload = $this->validBookingData();
        unset($payload['house_no'], $payload['barangay']);

        $this->actingAs($this->client)
            ->postJson(route('client.bookings.store'), $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['house_no', 'barangay']);
    }
}
