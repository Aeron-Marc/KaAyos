<?php

namespace Tests\Feature\Middleware;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AdminRoleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['web', 'auth', 'admin'])->get('/test-admin', fn () => response()->json(['ok' => true]));
    }

    public function test_admin_can_access_admin_route(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get('/test-admin')
            ->assertOk();
    }

    public function test_client_gets_403_on_admin_route(): void
    {
        $client = User::factory()->create(['role' => 'client']);

        $this->actingAs($client)
            ->get('/test-admin')
            ->assertStatus(403);
    }

    public function test_worker_gets_403_on_admin_route(): void
    {
        $worker = User::factory()->create(['role' => 'worker']);

        $this->actingAs($worker)
            ->get('/test-admin')
            ->assertStatus(403);
    }

    public function test_guest_gets_redirected_on_admin_route(): void
    {
        $this->get('/test-admin')
            ->assertStatus(302);
    }
}
