<?php

namespace Tests\Feature\Middleware;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class WorkerRoleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['web', 'auth', 'worker'])->get('/test-worker', fn () => response()->json(['ok' => true]));
    }

    public function test_worker_can_access_worker_route(): void
    {
        $worker = User::factory()->create(['role' => 'worker']);

        $this->actingAs($worker)
            ->get('/test-worker')
            ->assertOk();
    }

    public function test_client_gets_403_on_worker_route(): void
    {
        $client = User::factory()->create(['role' => 'client']);

        $this->actingAs($client)
            ->get('/test-worker')
            ->assertStatus(403);
    }

    public function test_admin_gets_403_on_worker_route(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get('/test-worker')
            ->assertStatus(403);
    }

    public function test_guest_gets_redirected_on_worker_route(): void
    {
        $this->get('/test-worker')
            ->assertStatus(302);
    }
}
