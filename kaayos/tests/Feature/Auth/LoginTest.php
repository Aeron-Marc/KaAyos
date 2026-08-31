<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'role' => 'client',
            'password' => bcrypt('secret123'),
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'secret123',
        ])->assertRedirect();

        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_wrong_password(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('secret123'),
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrongpassword',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_client_redirects_to_client_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'client']);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('client.dashboard'));
    }

    public function test_worker_redirects_to_worker_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'worker']);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('worker.dashboard'));
    }

    public function test_admin_redirects_to_admin_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));
    }

    public function test_account_locks_after_5_failed_attempts(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('secret123'),
            'failed_login_attempts' => 0,
            'locked_until' => null,
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => $user->email,
                'password' => 'wrongpassword',
            ]);
        }

        $user->refresh();
        $this->assertNotNull($user->locked_until, 'User should be locked after 5 failed attempts');
        $this->assertTrue($user->locked_until->isFuture());
    }

    public function test_locked_account_cannot_login(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('secret123'),
            'locked_until' => now()->addMinutes(10),
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'secret123',
        ])->assertSessionHas('error');

        $this->assertGuest();
    }

    public function test_lockout_expires_after_15_minutes(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('secret123'),
            'locked_until' => now()->subMinutes(16),
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'secret123',
        ])->assertRedirect();

        $this->assertAuthenticatedAs($user);
    }
}
