<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WorkerProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientWorkerListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_worker_listing_filters_by_active_availability_without_sql_errors(): void
    {
        $client = User::factory()->create(['role' => 'client']);

        $active = User::factory()->create(['role' => 'worker', 'first_name' => 'Active', 'last_name' => 'Worker']);
        WorkerProfile::create([
            'user_id' => $active->id,
            'availability' => [
                ['day' => 'Monday', 'active' => true, 'start' => '08:00', 'end' => '17:00'],
            ],
        ]);

        $inactive = User::factory()->create(['role' => 'worker', 'first_name' => 'Inactive', 'last_name' => 'Worker']);
        WorkerProfile::create([
            'user_id' => $inactive->id,
            'availability' => [
                ['day' => 'Monday', 'active' => false, 'start' => null, 'end' => null],
            ],
        ]);

        $this->actingAs($client)
            ->get(route('client.workers'))
            ->assertOk()
            ->assertSee('Active Worker')
            ->assertDontSee('Inactive Worker');
    }

    public function test_search_filters_by_active_availability_without_sql_errors(): void
    {
        $active = User::factory()->create(['role' => 'worker', 'first_name' => 'Searchable', 'last_name' => 'Active']);
        WorkerProfile::create([
            'user_id' => $active->id,
            'availability' => [
                ['day' => 'Monday', 'active' => true, 'start' => '08:00', 'end' => '17:00'],
            ],
        ]);

        $inactive = User::factory()->create(['role' => 'worker', 'first_name' => 'Searchable', 'last_name' => 'Inactive']);
        WorkerProfile::create([
            'user_id' => $inactive->id,
            'availability' => [
                ['day' => 'Monday', 'active' => false, 'start' => null, 'end' => null],
            ],
        ]);

        $this->get(route('search'))
            ->assertOk()
            ->assertSee('Searchable Active')
            ->assertDontSee('Searchable Inactive');
    }
}
